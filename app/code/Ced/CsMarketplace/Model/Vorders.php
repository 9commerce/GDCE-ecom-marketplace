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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model;

use Ced\CsMarketplace\Helper\Acl;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;


/**
 * Class Vorders
 * @package Ced\CsMarketplace\Model
 */
class Vorders extends \Ced\CsMarketplace\Model\FlatAbstractModel
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
     * @var
     */
    protected static $_states;

    /**
     * @var null
     */
    public $_vendorstatus = null;

    /**
     * @var null
     */
    protected $_items = null;

    /**
     * @var string
     */
    protected $_eventPrefix = 'csmarketplace_vorders';

    /**
     * @var string
     */
    protected $_eventObject = 'vorder';

    /**
     * @var Acl
     */
    protected $marketplaceAclHelper;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var
     */
    protected $customer;

    /**
     * @var
     */
    protected $_dataHelper;

    /**
     * @var
     */
    protected $_aclHelper;

    /**
     * Vorders constructor.
     * @param Acl $marketplaceAclHelper
     * @param OrderFactory $orderFactory
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Acl $marketplaceAclHelper,
        OrderFactory $orderFactory,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->marketplaceAclHelper = $marketplaceAclHelper;
        $this->orderFactory = $orderFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;

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
     * Retrieve vendor order states array
     *
     * @return array
     */
    public function getStates()
    {
        if (self::$_states === null) {
            self::$_states = [
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
                // self::STATE_REFUND => __('Refund'),
                // self::STATE_REFUNDED => __('Refunded'),
            ];
        }
        return self::$_states;
    }

    /**
     * Check vendor order pay action availability
     *
     * @return bool
     */
    public function canPay()
    {
        return $this->getOrderPaymentState() == Invoice::STATE_PAID
            &&
            $this->getPaymentState() == self::STATE_OPEN;
    }

    /**
     * Check vendor order cancel action availability
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->getPaymentState() == self::STATE_OPEN;
    }

    /**
     * Check vendor order refund action availability
     *
     * @return bool
     */
    public function canMakeRefund()
    {
        return $this->getOrderPaymentState() == Invoice::STATE_PAID
            &&
            $this->getPaymentState() == self::STATE_PAID;
    }

    /**
     * Check vendor order refund action availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->getOrderPaymentState() == Invoice::STATE_PAID
            &&
            $this->getPaymentState() == self::STATE_REFUND;
    }

    /**
     * Get Vordered Subtotal
     * return float
     */
    public function getPurchaseSubtotal()
    {
        $items = $this->getItemsCollection();
        $subtotal = 0;
        foreach ($items as $_item) {
            $subtotal += $_item->getRowTotal();
        }
        return $subtotal;
    }

    /**
     * Get Ordered Items associated to customer
     * return order_item_collection
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection|null
     */
    public function getItemsCollection($filterByTypes = array(), $nonChildrenOnly = false)
    {
        $vendorId = $this->getVendorId();
        $order = $this->getOrder();

        if ($this->_items === null) {
            $this->_items = $this->collectionFactory->create()
                ->setOrderFilter($order)
                ->addFieldToFilter('vendor_id', $vendorId);

            if ($filterByTypes) {
                $this->_items->filterByTypes($filterByTypes);
            }

            if ($nonChildrenOnly) {
                $this->_items->filterByParent();
            }

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    if ($item->getVendorId() == $vendorId) {
                        $item->setOrder($order);
                    }
                }
            }
        }

        return $this->_items;
    }

    /**
     * Get Ordered Items associated to customer
     * return order_item_collection
     * @param bool $incrementId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($incrementId = false)
    {
        if (!$incrementId) {
            $incrementId = $this->getOrderId();
        }

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        return $order;
    }

    /**
     * Get Vorder base Subtotal
     * return float
     */
    public function getBaseSubtotal()
    {
        $items = $this->getItemsCollection();
        $basesubtotal = 0;
        foreach ($items as $_item) {
            $basesubtotal += $_item->getBaseRowTotal();
        }
        return $basesubtotal;
    }

    /**
     * Get Vordered Grandtotal
     * return float
     */
    public function getPurchaseGrandTotal()
    {
        $items = $this->getItemsCollection();
        $grandtotal = 0;
        foreach ($items as $_item) {
            $grandtotal +=
                $_item->getRowTotal()
                + $_item->getTaxAmount()
                + $_item->getHiddenTaxAmount()
                + $_item->getWeeeTaxAppliedRowAmount()
                + $_item->getBaseDiscountTaxCompensationAmount()
                - $_item->getDiscountAmount();
        }
        return $grandtotal;
    }

    /**
     * Get Vordered base Grandtotal
     * return float
     */
    public function getBaseGrandTotal()
    {
        $items = $this->getItemsCollection();
        $basegrandtotal = 0;
        foreach ($items as $_item) {
            $basegrandtotal += $_item->getBaseRowTotal()
                + $_item->getBaseTaxAmount()
                + $_item->getBaseHiddenTaxAmount()
                + $_item->getBaseWeeeTaxAppliedRowAmount()
                + $_item->getBaseDiscountTaxCompensationAmount()
                - $_item->getBaseDiscountAmount();
        }
        return $basegrandtotal;
    }

    /**
     * Get Vordered tax
     * return float
     */
    public function getPurchaseTaxAmount()
    {
        $items = $this->getItemsCollection();
        $tax = 0;
        foreach ($items as $_item) {
            $tax += $_item->getTaxAmount() + $_item->getHiddenTaxAmount() + $_item->getWeeeTaxAppliedRowAmount();
        }
        return $tax;
    }

    /**
     * Get Vorder tax
     * return float
     */
    public function getBaseTaxAmount()
    {
        $items = $this->getItemsCollection();
        $tax = 0;
        foreach ($items as $_item) {
            $tax += $_item->getBaseTaxAmount() + $_item->getBaseHiddenTaxAmount() +
                $_item->getBaseWeeeTaxAppliedRowAmount();
        }
        return $tax;
    }

    /**
     * Get Vorder Discount
     * return float
     */
    public function getPurchaseDiscountAmount()
    {
        $items = $this->getItemsCollection();
        $discount = 0;
        foreach ($items as $_item) {
            $discount += $_item->getDiscountAmount();
        }
        return $discount;
    }

    /**
     * Get Vorder Discount
     * return float
     */
    public function getBaseDiscountAmount()
    {
        $items = $this->getItemsCollection();
        $discount = 0;
        foreach ($items as $_item) {
            $discount += $_item->getBaseDiscountAmount();
        }
        return $discount;
    }

    /**
     * Calculate the commission fee
     *
     * @return Vorders
     */
    public function collectCommission()
    {
        if ($this->getData('vendor_id') && $this->getData('base_to_global_rate') && $this->getData('order_total')) {
            $order = $this->getCurrentOrder();

            $helper = $this->marketplaceAclHelper->setStoreId($order->getStoreId())
                ->setOrder($order)
                ->setVendorId($this->getData('vendor_id'));

            $commissionSetting = $helper->getCommissionSettings($this->getData('vendor_id'));
            $commissionSetting['item_commission'] = $this->getData('item_commission');

            $commission =
                $helper->calculateCommission(
                    $this->getData('order_total'),
                    $this->getData('base_order_total'),
                    $this->getData('base_to_global_rate'),
                    $commissionSetting
                );

            $this->setShopCommissionTypeId($commissionSetting['type']);
            $this->setShopCommissionRate($commissionSetting['rate']);
            $this->setShopCommissionBaseFee($commission['base_fee']);
            $this->setShopCommissionFee($commission['fee']);
            $this->setCreatedAt($this->dateTime->gmtDate());
            $this->setPaymentState(self::STATE_OPEN);
            if (isset($commission['item_commission'])) {
                $this->setItemsCommission($commission['item_commission']);
            }
            $this->setOrderPaymentState(Invoice::STATE_OPEN);
        }

        return $this;
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vorders');
    }

    /**
     * @param $attributes
     * @return $this
     */
    public function loadByColumns($attributes)
    {
        $data = $this->getResource()->loadByColumns($attributes);
        if ($data) {
            $this->setData($data);
        }
        return $this;
    }
}
