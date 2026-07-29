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

namespace Ced\CsMarketplace\Observer;


use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\Vorders;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class OrderCancelAfter
 * @package Ced\CsMarketplace\Observer
 */
Class OrderCancelAfter implements ObserverInterface
{

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $_vendorOrderCollectionFactory;

    /**
     * @var Data
     */
    protected $_marketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $_marketplaceMail;

    /**
     * OrderCancelAfter constructor.
     * @param \Ced\CsMarketplace\Helper\Mail $marketplaceMail
     * @param Data $marketplaceHelper
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vendorOrderCollectionFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Mail $marketplaceMail,
        Data $marketplaceHelper,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vendorOrderCollectionFactory
    ) {
        $this->_marketplaceMail = $marketplaceMail;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
    }

    /**
     * Cancel the asscociated vendor order
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->_marketplaceHelper->logProcessedData(
            $order->getIncrementId(),
            Data::SALES_ORDER_CANCELED
        );

        try {
            $vOrders = $this->_vendorOrderCollectionFactory->create()
                ->addFieldToFilter('order_id', array('eq' => $order->getIncrementId()));
            if (count($vOrders) > 0) {
                foreach ($vOrders as $vOrder) {
                    if ($vOrder->canCancel() && $vOrder->getOrderPaymentState() == Vorders::STATE_OPEN) {
                        $vOrder->setOrderPaymentState(Invoice::STATE_CANCELED);
                        $vOrder->setPaymentState(Vorders::STATE_CANCELED);
                        $vOrder->save();
                    } else if ($vOrder->canMakeRefund()) {
                        $vOrder->setPaymentState(Vorders::STATE_REFUND);
                        $vOrder->save();
                    }

                    $this->_marketplaceHelper->logProcessedData(
                        $vOrder->getData(),
                        Data::VORDER_CANCELED
                    );

                    $this->_marketplaceMail->sendOrderEmail(
                        $order,
                        Vorders::ORDER_CANCEL_STATUS,
                        $vOrder->getVendorId(),
                        $vOrder
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_marketplaceHelper->logException($e);
        }
        return $this;
    }
}