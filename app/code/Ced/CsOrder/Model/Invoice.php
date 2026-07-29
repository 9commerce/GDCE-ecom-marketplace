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

class Invoice extends \Ced\CsMarketplace\Model\FlatAbstractModel
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

    const STATE_PARTIALLY_PAID = 6;

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
    protected $_eventPrefix = 'csorder_invoice';

    /**
     * @var string
     */
    protected $_eventObject = 'vinvoice';

    /**
     * @var null
     */
    public $_vendorstatus = null;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
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
     * Invoice constructor.
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItems
     * @param \Magento\Sales\Model\ResourceModel\Order\Item $orderItemsResource
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
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
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->orderItems = $orderItems;
        $this->_orderItemsResource = $orderItemsResource;
        $this->vordersFactory = $vordersFactory;
        $this->csorderHelper = $csorderHelper;
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
        $this->_init(\Ced\CsOrder\Model\ResourceModel\Invoice::class);
    }

    /**
     * @param $invoice
     * @return bool
     */
    public function canInvoiceIncludeShipment($invoice)
    {
        if (is_object($invoice)) {
            $vendorId = $this->vordersFactory->create()->getVendorId();
            $attributes = [
                'invoice_order_id'=> $invoice->getOrderId(),
                'vendor_id'=> $vendorId,
                'shipping_code'=> ['notnull' => true]
            ];
            $invoicedCollection = $this->loadByColumns($attributes);
            if (!$invoicedCollection->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $invoice
     * @param bool $view
     * @return mixed
     */
    public function updateTotal($invoice, $view = true)
    {
        $vorder = $this->vordersFactory->create()
            ->setVendorId($this->getVendorId())->getVorderByInvoice($invoice);

        if (!$this->_registry->registry('current_vorder')) {
            $this->_registry->register('current_vorder', $vorder);
        }
        $helperData = $this->csorderHelper;
        if (!is_object($vorder)) {
            return $invoice;
        }

        if (!$vorder->isAdvanceOrder() && $vorder->getCode()) {
            $invoice->setOrder($vorder->getOrder(false, true));
            if ($view && $vInvoice = $this->updateInvoiceGridTotal($invoice)) {
                $invoice->setShippingAmount($vInvoice->getShippingAmount());
                $invoice->setBaseShippingAmount($vInvoice->getBaseShippingAmount());
            } elseif ($this->canInvoiceIncludeShipment($invoice)) {
                $invoice->setShippingAmount($vorder->getShippingAmount());
                $invoice->setBaseShippingAmount($vorder->getBaseShippingAmount());
            }

            $baseSubtotal = $this->getItemBaseSubtotalByInvoice($invoice);
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $baseDiscount = $this->getItemBaseDiscountByInvoice($invoice);
            $discount = $this->getItemDiscountByInvoice($invoice);
            $invoice->setBaseSubtotal($baseSubtotal);
            $invoice->setSubtotal($subtotal);
            $baseTax = $this->getItemBaseTaxByInvoice($invoice);
            $tax = $this->getItemTaxByInvoice($invoice);
            $invoice->setBaseTaxAmount($baseTax);
            $invoice->setTaxAmount($tax);
            $invoice->setBaseDiscountAmount($baseDiscount);
            $invoice->setDiscountAmount($discount);
            $invoice->setGrandTotal($subtotal - $discount + $tax + $invoice->getShippingAmount());
            $invoice->setBaseGrandTotal($baseSubtotal - $baseDiscount + $baseTax + $invoice->getBaseShippingAmount());
        }

        if (!$helperData->canShowShipmentBlock($vorder)) {
            $invoice->setShippingAmount(0);
            $invoice->setBaseShippingAmount(0);
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $baseSubtotal = $this->getItemBaseSubtotalByInvoice($invoice);
            $discount = $this->getItemDiscountByInvoice($invoice);
            $baseDiscount = $this->getItemBaseDiscountByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $invoice->setBaseSubtotal($baseSubtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $baseTax = $this->getItemBaseTaxByInvoice($invoice);
            $invoice->setTaxAmount($tax);
            $invoice->setBaseTaxAmount($baseTax);
            $invoice->setDiscountAmount($discount);
            $invoice->setBaseDiscountAmount($baseDiscount);
            $invoice->setBaseGrandTotal($baseSubtotal - $baseDiscount + $baseTax + $invoice->getBaseShippingAmount());
            $invoice->setGrandTotal($subtotal - $discount + $tax + $invoice->getShippingAmount());
        }

        return $invoice;
    }

    /**
     * @param $invoice
     * @return $this|false
     */
    public function updateInvoiceGridTotal($invoice)
    {
        if (is_object($invoice)) {
            $vendorId = $this->getVendorId();
            $attributes = [
                'invoice_id'=> $invoice->getId(),
                'vendor_id'=> $vendorId,
                'shipping_code'=> ['notnull' => true]
            ];
            $invoicedCollection = $this->loadByColumns($attributes);
            if ($invoicedCollection->getId()) {
                return $invoicedCollection;
            }
        }
        return false;
    }

    /**
     * @param $invoice
     * @return mixed
     */
    public function updateTotalGrid($invoice)
    {
        $vorder = $this->vordersFactory->create()->getVorderByInvoice($invoice);
        $helperData = $this->csorderHelper;
        if (!is_object($vorder)) {
            return $invoice;
        }
        if (!$vorder->isAdvanceOrder() && $vorder->getCode()) {
            $invoice->setOrder($vorder->getOrder(false, true));
            if ($vInvoice = $this->updateInvoiceGridTotal($invoice)) {
                $invoice->setShippingAmount($vInvoice->getShippingAmount());
                $invoice->setBaseShippingAmount($vInvoice->getBaseShippingAmount());
            }
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $invoice->setTaxAmount($tax);
            $invoice->setBaseTaxAmount($tax);
            $invoice->setGrandTotal($subtotal + $tax + $invoice->getShippingAmount());
        }

        if (!$helperData->canShowShipmentBlock($vorder)) {
            $invoice->setShippingAmount(0);
            $invoice->setBaseShippingAmount(0);
            $subtotal = $this->getItemSubtotalByInvoice($invoice);
            $invoice->setSubtotal($subtotal);
            $tax = $this->getItemTaxByInvoice($invoice);
            $invoice->setTaxAmount($tax);
            $invoice->setGrandTotal($subtotal + $tax + $invoice->getShippingAmount());
        }
        return $invoice;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemSubtotalByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getRowTotal();
        }
        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemBaseSubtotalByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseRowTotal();
        }
        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemTaxByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getTaxAmount();
        }
        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemBaseTaxByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseTaxAmount();
        }
        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemDiscountByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getDiscountAmount();
        }
        return $total;
    }

    /**
     * @param $invoice
     * @return int
     */
    public function getItemBaseDiscountByInvoice($invoice)
    {
        $items = $invoice->getAllItems();
        $vendorId = $this->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            $vendorProduct = $this->orderItems->create();
            $this->_orderItemsResource->load($vendorProduct, $_item->getOrderItemId());
            if ($vendorProduct->getVendorId() != $vendorId) {
                continue;
            }
            $total += $_item->getBaseDiscountAmount();
        }
        return $total;
    }

    //@codingStandardsIgnoreStart
    /**
     * @return array
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = [
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
                self::STATE_PARTIALLY_PAID => __('Partially Paid'),
            ];
        }
        return self::$_states;
    }
    //@codingStandardsIgnoreEnd

    /**
     * @param $attributes
     * @return $this
     */
    public function loadByColumns($attributes)
    {
        $data=$this->getResource()->loadByColumns($attributes);
        if ($data) {
            $this->setData($data);
        }
        return $this;
    }
}
