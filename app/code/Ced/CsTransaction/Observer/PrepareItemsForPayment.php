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

namespace Ced\CsTransaction\Observer;

use Ced\CsTransaction\Model\Items;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PrepareItemsForPayment implements ObserverInterface
{
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $_vordersCollectionFactory;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $_csorderHelper;

    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_vtordersFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_vtordersResource;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vtItemsCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_salesItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item
     */
    protected $_salesItemResource;

    /**
     * PrepareItemsForPayment constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsTransaction\Model\ItemsFactory $vtordersFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items $vtordersResource
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $salesItemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item $salesItemResource
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Model\ItemsFactory $vtordersFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $vtordersResource,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory,
        \Magento\Sales\Model\Order\ItemFactory $salesItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item $salesItemResource
    ) {
        $this->_vordersCollectionFactory = $vordersCollectionFactory;
        $this->_csorderHelper = $csorderHelper;
        $this->_vtordersFactory = $vtordersFactory;
        $this->_vtordersResource = $vtordersResource;
        $this->_vtItemsCollectionFactory = $vtItemsCollectionFactory;
        $this->_salesItemFactory = $salesItemFactory;
        $this->_salesItemResource = $salesItemResource;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_csorderHelper->isActive()) {
            return $this;
        }
        try {
            $invoice = $observer->getEvent()->getInvoice();
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $quoteItem = $this->_salesItemFactory->create();
                $this->_salesItemResource->load($quoteItem, $item->getOrderItem()->getId());
                $vorder = $this->_vordersCollectionFactory->create()
                        ->addFieldToFilter('order_id', $invoice->getOrder()->getIncrementId())
                        ->addFieldToFilter('vendor_id', $item->getVendorId())->getFirstItem();
                $itemCollection = $this->_vtItemsCollectionFactory->create()
                        ->addFieldToFilter('vendor_id', $item->getVendorId())
                        ->addFieldToFilter('parent_id', $vorder->getId())
                        ->addFieldToFilter('order_item_id', $item->getOrderItemId());

                if (empty($itemCollection->getData())) {
                    if (($vorder->getVendorId() > 0) && !empty($vorder->getData())) {
                        $this->saveOrderItem($item, $invoice, $vorder, $quoteItem);
                    }
                } elseif (!empty($itemCollection->getData())) {
                    foreach ($itemCollection as $items) {
                        $itemsFee = json_decode($vorder->getItemsCommission()??'', true);
                        $saveItems = $this->_vtordersFactory->create();
                        $this->_vtordersResource->load($saveItems, $items->getId());
                        $saveItems->setItemPaymentState(Items::STATE_READY_TO_PAY);
                        $saveItems->setQtyReadyToPay($items->getQtyReadyToPay() + $item->getQty());
                        $saveItems->setTotalInvoicedAmount($items->getTotalInvoicedAmount() + $item->getRowTotal());
                        $total = $this->getRowTotalFeeAmount($item);
                        if (isset($itemsFee[$quoteItem->getQuoteItemId()]['base_fee'])) {
                            $itemCommission =
                                    $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem->getQtyOrdered();
                            $saveItems->setItemFee(
                                $saveItems->getItemFee() + ($total - ($itemCommission * $item->getQty()))
                            );
                            $saveItems->setItemCommission(
                                $saveItems->getItemCommission() + ($itemCommission * $item->getQty())
                            );
                        }

                        $saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() + $item->getBaseRowTotal());
                        $saveItems->setRowTotal($saveItems->getRowTotal() + $item->getRowTotal());
                        $saveItems->setTotalInvoicedAmount($saveItems->getTotalInvoicedAmount() + $item->getRowTotal());
                        $saveItems->setQtyForPayNow($items->getQtyReadyToPay() + $item->getQty());
                        $this->_vtordersResource->save($saveItems);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $this;
    }

    /**
     * @param $item
     * @param $invoice
     * @param $vorder
     * @param $quoteItem
     * @throws \Exception
     */
    public function saveOrderItem($item, $invoice, $vorder, $quoteItem)
    {
        $itemsFee = json_decode($vorder->getItemsCommission()??'', true);
        $vorderItem = $this->_vtordersFactory->create();
        $vorderItem->setParentId($vorder->getId());
        $vorderItem->setOrderItemId($item->getOrderItemId());
        $vorderItem->setOrderId($invoice->getOrder()->getId());
        $vorderItem->setOrderIncrementId($invoice->getOrder()->getIncrementId());
        $vorderItem->setVendorId($vorder->getVendorId());
        $vorderItem->setCurrency($vorder->getCurrency());
        $vorderItem->setBaseRowTotal($item->getBaseRowTotal());
        $vorderItem->setRowTotal($item->getRowTotal());
        $vorderItem->setSku($item->getSku());
        $vorderItem->setShopCommissionTypeId($vorder->getShopCommissionTypeId());
        $vorderItem->setShopCommissionRate($vorder->getShopCommissionRate());
        $vorderItem->setShopCommissionBaseFee($vorder->getShopCommissionBaseFee());
        $vorderItem->setShopCommissionFee($vorder->getShopCommissionFee());
        $vorderItem->setProductQty($item->getQtyOrdered());
        $vorderItem->setItemPaymentState(false);
        $total = $this->getRowTotalFeeAmount($item);
        if (isset($itemsFee[$quoteItem->getQuoteItemId()])) {
            $itemCommission = $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem->getQtyOrdered();
            $vorderItem->setItemFee($total - ($itemCommission * $item->getQty()));
            $vorderItem->setItemCommission($itemCommission * $item->getQty());
        }else{
            $vorderItem->setItemFee($total);
        }

        $vorderItem->setQtyOrdered($quoteItem->getQtyOrdered());

        $vorderItem->setItemPaymentState(Items::STATE_READY_TO_PAY);
        $vorderItem->setQtyReadyToPay($item->getQty());
        $vorderItem->setTotalInvoicedAmount($item->getRowTotal());
        $vorderItem->setIsRequested(Items::STATE_IS_NOT_REQUESTED);
        $vorderItem->setQtyForPayNow($item->getQty());
        $this->_vtordersResource->save($vorderItem);
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getRowTotalFeeAmount($item)
    {
        $amount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount();
        return $amount;
    }
}
