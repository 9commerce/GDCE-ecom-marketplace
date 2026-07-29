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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Model;

class Items extends \Magento\Framework\Model\AbstractModel
{
    const STATE_IS_NOT_REQUESTED = 0;

    const STATE_READY_TO_PAY = 1;

    const STATE_PAID = 2;

    const STATE_READY_TO_REFUND = 3;

    const STATE_REFUNDED = 4;

    /**
     * @var string
     */
    protected $_eventPrefix = 'cstransaction_vorder_items';

    /**
     * @var string
     */
    protected $_eventObject = 'vorder_item';

    /**
     * @var null
     */
    protected $_items = null;

    /**
     * @var
     */
    protected static $_states;

    /**
     * @var ResourceModel\Items\CollectionFactory
     */
    protected $cstransactionitemsCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $itemCollection;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $acl;

    /**
     * Items constructor.
     * @param ResourceModel\Items\CollectionFactory $cstransactionitemsCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $cstransactionitemsCollection,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cstransactionitemsCollection = $cstransactionitemsCollection;
        $this->itemCollection = $itemCollection;
        $this->orderFactory = $orderFactory;
        $this->acl = $acl;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     *
     */
    public function _construct()
    {
        $this->_init(\Ced\CsTransaction\Model\ResourceModel\Items::class);
    }

    /**
     * @return array
     */
    // @codingStandardsIgnoreStart
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = [];
        }
        return self::$_states;
    }

    /**
     * @param $vendorId
     * @param $orderid
     * @return bool|string
     */
    public function canPay($vendorId, $orderid)
    {
        $collection = $this->cstransactionitemsCollection->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('order_increment_id', ['eq' => $orderid])
            ->addFieldToFilter('qty_ready_to_pay', ['gt' => 0]);
        $ids = '';
        foreach ($collection as $item) {
            if ($item->getQtyOrdered() == $item->getQtyReadyToPay() + $item->getQtyRefunded()) {
                $ids .= $item->getOrderId() . ',';
            }
        }
        if ($ids == '') {
            return false;
        }
        return $ids;
    }

    /**
     * @param $vorderItem
     */
    public function setQtyForRefund($vorderItem)
    {
        $maxQtyCanBeRefunded = $vorderItem->getQtyPaid() - $vorderItem->getQtyRefunded();
        $pendingQtyToRefund = $vorderItem->getQtyPendingToRefund();

        if ($maxQtyCanBeRefunded > 0) {
            if ($pendingQtyToRefund <= $maxQtyCanBeRefunded) {
                $vorderItem->setQtyReadyToRefund($pendingQtyToRefund);
                $vorderItem->setQtyPendingToRefund(0);
            } else {
                $vorderItem->setQtyReadytToRefund($maxQtyCanBeRefunded);
                $vorderItem->setQtyPendingToRefund($pendingQtyToRefund - $maxQtyCanBeRefunded);
            }
        }
        /* for multiple qty refund */
        if ($maxQtyCanBeRefunded == 0) {
            $vorderItem->setQtyPendingToRefund(0);
        }
        $vorderItem->save();
    }

    /**
     * @param $vorderItem
     */
    public function setQtyForPay($vorderItem)
    {
        $qtyReadyToRefund = $vorderItem->getQtyReadyToRefund();
        $qtyReadyToPay = $vorderItem->getQtyReadyToPay();

        if ($qtyReadyToRefund >= $qtyReadyToPay) {
            $vorderItem->setQtyReadyToRefund($qtyReadyToRefund - $qtyReadyToPay);
            $vorderItem->setQtyReadyToPay(0);
        } elseif ($qtyReadyToRefund < $qtyReadyToPay) {
            $vorderItem->setQtyReadyToRefund(0);
            $vorderItem->setQtyReadyToPay($qtyReadyToPay - $qtyReadyToRefund);
        }
        $vorderItem->save();
    }

    /**
     * Check vendor order cancel action availability
     * @return bool
     */
    public function canCancel()
    {
        return $this->getPaymentState() == self::STATE_OPEN;
    }

    /**
     * Check vendor order refund action availability
     * @return bool
     */
    public function canMakeRefund()
    {
        return $this->getOrderPaymentState() == \Magento\Sales\Model\Order\Invoice::STATE_PAID
            &&
            $this->getPaymentState() == self::STATE_PAID;
    }

    /**
     * @param $vendorId
     * @param $orderid
     * @return bool|string
     */
    public function canRefund($vendorId, $orderid)
    {
        $collection = $this->cstransactionitemsCollection->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('order_increment_id', ['eq' => $orderid])
            ->addFieldToFilter('qty_ready_to_refund', ['gt' => 0]);
        $ids = '';
        foreach ($collection as $item) {
            $ids .= $item->getId() . ',';
        }
        if ($ids == '') {
            return false;
        }
        return $ids;
    }

    /**
     * @param array $filterByTypes
     * @param bool $nonChildrenOnly
     * @return null
     */
    public function getItemsCollection($filterByTypes = [], $nonChildrenOnly = false)
    {
        $vendorId = $this->getVendorId();

        $order = $this->getOrder();
        if (is_null($this->_items)) {
            $this->_items = $this->itemCollection
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
     * @param bool $incrementId
     * @return mixed
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
     * Get Vordered base Subtotal
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
     * Get Vordered base Grandtotal
     * return float
     */
    public function getBaseGrandTotal()
    {
        $items = $this->getItemsCollection();
        $basegrandtotal = 0;
        foreach ($items as $_item) {
            $basegrandtotal += $_item->getBaseRowTotal() +
                $_item->getBaseTaxAmount() +
                $_item->getBaseHiddenTaxAmount() +
                $_item->getBaseWeeeTaxAppliedRowAmount() -
                $_item->getBaseDiscountAmount();
        }
        return $basegrandtotal;
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
            $grandtotal += $_item->getRowTotal() +
                $_item->getTaxAmount() +
                $_item->getHiddenTaxAmount() +
                $_item->getWeeeTaxAppliedRowAmount() -
                $_item->getDiscountAmount();
        }
        return $grandtotal;
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
            $tax += $_item->getTaxAmount() +
                $_item->getHiddenTaxAmount() +
                $_item->getWeeeTaxAppliedRowAmount();
        }
        return $tax;
    }

    /**
     * Get Vordered Discount
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
     * Get Vordered tax
     * return float
     */
    public function getBaseTaxAmount()
    {
        $items = $this->getItemsCollection();
        $tax = 0;
        foreach ($items as $_item) {
            $tax += $_item->getBaseTaxAmount() +
                $_item->getBaseHiddenTaxAmount() +
                $_item->getBaseWeeeTaxAppliedRowAmount();
        }
        return $tax;
    }

    /**
     * Calculate the commission fee
     * @return void
     */
    public function collectCommission()
    {
        if ($this->getData('vendor_id') && $this->getData('base_to_global_rate') && $this->getData('order_total')) {
            $order = $this->getOrder();
            $helper = $this->acl->setStoreId($order->getStoreId())->setOrder($order);
            $commissionSetting = $helper->getCommissionSettings($this->getData('vendor_id'));
            $commission = $helper->calculateCommission(
                $this->getData('order_total'),
                $this->getData('base_order_total'),
                $this->getData('base_to_global_rate'),
                $commissionSetting
            );
            $this->setShopCommissionTypeId($commissionSetting['type']);
            $this->setShopCommissionRate($commissionSetting['rate']);
            $this->setShopCommissionBaseFee($commission['base_fee']);
            $this->setShopCommissionFee($commission['fee']);
            $this->setPaymentState(self::STATE_OPEN);
            $this->setOrderPaymentState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);
        }
    }

    /**
     * Get Vordered Discount
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
     * @param $vendorId
     * @param $orderid
     * @return mixed
     */
    public function getCsTransactionItemsCollection($vendorId, $orderid)
    {
        $collection = $this->cstransactionitemsCollection->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('order_increment_id', ['eq' => $orderid]);
        return $collection;
    }
}
