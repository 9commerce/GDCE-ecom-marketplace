<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model;

use Magento\Framework\Api\AttributeValueFactory;

class CreditmemoGrid extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * Payment states
     */
    const STATE_OPEN = 1;

    const STATE_PAID = 2;

    const STATE_CANCELED = 3;

    const STATE_REFUND = 4;

    const STATE_REFUNDED = 5;

    const ORDER_NEW_STATUS = 1;

    const ORDER_CANCEL_STATUS = 3;

    /**
     * @var null
     */
    protected $_items = null;

    /**
     * @var
     */
    protected static $_states;

    /**
     * @var string
     */
    protected $_eventPrefix = 'csorder_creditmemo';

    /**
     * @var string
     */
    protected $_eventObject = 'vcreditmemo';

    /**
     * @var null
     */
    public $_vendorstatus = null;

    /**
     * @var VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItems;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item
     */
    protected $_orderItemsResource;

    /**
     * CreditmemoGrid constructor.
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItems
     * @param \Magento\Sales\Model\ResourceModel\Order\Item $orderItemsResource
     * @param VordersFactory $vordersFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory $orderItems,
        \Magento\Sales\Model\ResourceModel\Order\Item $orderItemsResource,
        \Ced\CsOrder\Model\VordersFactory $vordersFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->vordersFactory = $vordersFactory;
        $this->csorderHelper = $csorderHelper;
        $this->orderItems = $orderItems;
        $this->_orderItemsResource = $orderItemsResource;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Ced\CsOrder\Model\ResourceModel\Creditmemo::class);
    }

    /**
     * @param $creditmemo
     * @return mixed
     */
    public function updateTotal($creditmemo)
    {
        $vorder = $this->vordersFactory->create()
            ->setVendorId($this->getVendorId())->getVorderByCreditmemo($creditmemo);

        if (!$this->_registry->registry('current_vorder')) {
            $this->_registry->register('current_vorder', $vorder);
        }

        if (!$vorder->isAdvanceOrder() && $vorder->getShippingAmount() >= 0) {
            $creditmemo->setOrder($vorder->getOrder(false, true));
            if ($creditmemo->getShippingAmount() > 0) {
                $creditmemo->setShippingAmount($vorder->getShippingAmount());
                $creditmemo->setBaseShippingAmount($vorder->getBaseShippingAmount());
            }
            $subtotal = $this->getItemSubtotalByCreditmemo($creditmemo);
            $baseDiscount = $this->getItemBaseDiscountByCreditmemo($creditmemo);
            $creditmemo->setSubtotal($subtotal);
            $tax = $this->getItemTaxByCreditmemo($creditmemo);
            $creditmemo->setTaxAmount($tax);

            $adjustment = $creditmemo->getAdjustment();
            $grandTotal = $subtotal - $baseDiscount
                + $tax
                + $adjustment
                + $creditmemo->getBaseShippingAmount();

            $creditmemo->setGrandTotal($grandTotal);
        }

        if (!$this->csorderHelper->canShowShipmentBlock($vorder)) {
            $creditmemo->setShippingAmount(0);
            $creditmemo->setBaseShippingAmount(0);
            $subtotal = $this->getItemSubtotalByCreditmemo($creditmemo);

            $creditmemo->setSubtotal($subtotal);
            $tax = $this->getItemTaxByCreditmemo($creditmemo);
            $creditmemo->setTaxAmount($tax);

            $adjustment = $creditmemo->getAdjustment();
            $grandTotal = $subtotal - $baseDiscount
                + $tax
                + $adjustment
                + $creditmemo->getBaseShippingAmount();

            $creditmemo->setGrandTotal($grandTotal);
        }

        return $creditmemo;
    }

    /**
     * @param $creditmemo
     * @return int
     */
    public function getItemSubtotalByCreditmemo($creditmemo)
    {
        $items = $creditmemo->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ((is_object($_item->getOrderItem())
                    && $_item->getOrderItem()->getParentItem())
                    || $vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseRowTotal();
        }
        return $total;
    }

    /**
     * @param $creditmemo
     * @return int
     */
    public function getItemTaxByCreditmemo($creditmemo)
    {
        $items = $creditmemo->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ((is_object($_item->getOrderItem())
                    && $_item->getOrderItem()->getParentItem())
                    || $vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseTaxAmount();
        }
        return $total;
    }

    /**
     * @param $creditmemo
     * @return int
     */
    public function getItemDiscountByCreditmemo($creditmemo)
    {
        $items = $creditmemo->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ((is_object($_item->getOrderItem())
                    && $_item->getOrderItem()->getParentItem())
                    || $vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getDiscountAmount();
        }

        return $total;
    }

    /**
     * @param $creditmemo
     * @return int
     */
    public function getItemBaseDiscountByCreditmemo($creditmemo)
    {
        $items = $creditmemo->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ((is_object($_item->getOrderItem())
                    && $_item->getOrderItem()->getParentItem())
                    || $vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseDiscountAmount();
        }

        return $total;
    }
}
