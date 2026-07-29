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

use Ced\CsMarketplace\Model\Vorders;
use Ced\CsTransaction\Model\Items;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PrepareItemsForRefund implements ObserverInterface
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
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $salesItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item
     */
    protected $salesItemResource;

    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_vtItemsFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_vtItemsResource;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vtItemsCollectionFactory;

    /**
     * PrepareItemsForRefund constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Sales\Model\Order\ItemFactory $salesItemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item $salesItemResource
     * @param \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Sales\Model\Order\ItemFactory $salesItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item $salesItemResource,
        \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory
    ) {
        $this->_vordersCollectionFactory = $vordersCollectionFactory;
        $this->_csorderHelper = $csorderHelper;
        $this->salesItemFactory = $salesItemFactory;
        $this->salesItemResource = $salesItemResource;
        $this->_vtItemsFactory = $vtItemsFactory;
        $this->_vtItemsResource = $vtItemsResource;
        $this->_vtItemsCollectionFactory = $vtItemsCollectionFactory;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_csorderHelper->isActive()) {
            return $this;
        }
        $creditmemo = $observer->getCreditmemo();
        $quoteitemid = [];
        $creditMemoItems = [];
        $qtyrefunded = 0;

        try {
            foreach ($creditmemo->getAllItems() as $item) {
                $quoteItem = $this->salesItemFactory->create();
                $this->salesItemResource->load($quoteItem, $item->getOrderItemId());

                $paymentItemcollection = $this->_vtItemsCollectionFactory->create()
                            ->addFieldToFilter('order_id', $creditmemo->getOrderId())
                            ->addFieldToFilter('order_item_id', $item->getOrderItemId())
                            ->addFieldToFilter('is_requested', ['neq' => '2']);

                foreach ($paymentItemcollection as $items) {
                    $vorder = $this->_vordersCollectionFactory->create()
                                ->addFieldToFilter('order_id', $creditmemo->getOrder()->getIncrementId())
                                ->addFieldToFilter('vendor_id', $items->getVendorId())
                                ->getFirstItem();
                    $can_refund = false;
                    if ($items->getItemPaymentState() != Items::STATE_PAID ||
                                $items->getItemPaymentState() == Items::STATE_READY_TO_PAY) {
                        $itemsFee = json_decode($vorder->getItemsCommission(), true);
                        $saveItems = $this->_vtItemsFactory->create();
                        $this->_vtItemsResource->load($saveItems, $items->getId());
                        $quoteitemid[] = $items->getOrderItemId();

                        $saveItems->setQtyReadyToPay($items->getQtyReadyToPay() - $item->getQty());
                        $saveItems->setTotalCreditmemoAmount(
                            $items->getTotalCreditmemoAmount() + $item->getBaseRowTotal()
                        );
                        $totalAmount = $this->getTotalAmount($item);
                        if (isset($itemsFee[$quoteItem->getQuoteItemId()])) {
                            $itemCommission =
                                        $itemsFee[$quoteItem->getQuoteItemId()] ['base_fee'] / $quoteItem
                                            ->getQtyOrdered();
                            $saveItems->setItemFee(
                                $saveItems->getItemFee() - ($totalAmount - ($itemCommission * $item->getQty()))
                            );
                            $saveItems->setItemCommission(
                                $saveItems->getItemCommission() - ($itemCommission * $item->getQty())
                            );
                            $saveItems->setAmountRefunded(
                                $saveItems->getAmountRefunded() + ($totalAmount - ($itemCommission * $item->getQty()))
                            );
                        }

                        $saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() - $item->getBaseRowTotal());
                        $saveItems->setRowTotal($saveItems->getRowTotal() - $item->getRowTotal());
                        $saveItems->setQtyReadyToRefund($saveItems->getQtyReadyToRefund() + $item->getQty());
                        $saveItems->setQtyRefunded($saveItems->getQtyRefunded() + $item->getQty());

                        if ($items->getQtyReadyToPay() == $item->getQty()) {
                            $saveItems->setIsRequested(Items::STATE_PAID);
                        }
                        $this->_vtItemsResource->save($saveItems);
                        $creditMemoItems[$item->getOrderItemId()] = $item->getQty();
                    } else {
                        $can_refund = true;
                        $itemsFee = json_decode($vorder->getItemsCommission(), true);
                        $saveItems = $this->_vtItemsFactory->create();
                        $this->_vtItemsResource->load($saveItems, $items->getId());
                        $quoteitemid[] = $items->getOrderItemId();
                        $qtyrefunded += $item->getQty();

                        $saveItems->setQtyReadyToPay($items->getQtyReadyToPay() - $item->getQty());
                        $saveItems->setTotalCreditmemoAmount(
                            $items->getTotalCreditmemoAmount() + $item->getBaseRowTotal()
                        );
                        $totalAmount = $this->getTotalAmount($item);
                        if (isset($itemsFee[$quoteItem->getQuoteItemId()])) {
                            $itemCommission =
                                        $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem
                                            ->getQtyOrdered();
                            $saveItems->setItemFee(
                                floatval($saveItems->getItemFee()) -
                                        (floatval($totalAmount) - (floatval($itemCommission * $item->getQty())))
                            );
                            $saveItems->setAmountRefunded(
                                floatval($saveItems->getAmountRefunded()) +
                                        floatval($totalAmount) -
                                        (floatval($itemCommission * $item->getQty()))
                            );
                            $saveItems->setAmountReadyToRefund(
                                floatval($saveItems->getAmountReadyToRefund()) +
                                        floatval($totalAmount) -
                                        (floatval($itemCommission * $item->getQty()))
                            );
                        }

                        $saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() - $item->getBaseRowTotal());
                        $saveItems->setRowTotal($saveItems->getRowTotal() - $item->getRowTotal());
                        $saveItems->setQtyReadyToRefund($saveItems->getQtyReadyToRefund() + $item->getQty());

                        $saveItems->setQtyRefunded($saveItems->getQtyRefunded() + $item->getQty());

                        $saveItems->setItemPaymentState(Items::STATE_READY_TO_REFUND);
                        if ($items->getQtyReadyToPay() == $item->getQty()) {
                            $saveItems->setIsRequested(Items::STATE_PAID);
                        }
                        $this->_vtItemsResource->save($saveItems);
                        $creditMemoItems[$item->getOrderItemId()] = $item->getQty();
                    }
                    $salesItem = $this->salesItemFactory->create();
                    $this->salesItemResource->load($salesItem, $items->getOrderItemId());
                    $qtyordered =  $salesItem->getQtyOrdered();
                    $qtyrefunded = $saveItems->getQtyRefunded();
                    if ($qtyordered == $qtyrefunded) {
                        $vorder->setPaymentState(Vorders::STATE_CANCELED);
                        $vorder->save();
                    } else {
                        $vorder->setPaymentState(Vorders::STATE_REFUND);
                        $vorder->save();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getTotalAmount($item)
    {
        $amount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount();
        return $amount;
    }
}
