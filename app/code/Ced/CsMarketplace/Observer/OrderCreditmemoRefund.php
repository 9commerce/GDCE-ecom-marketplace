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
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;
use Ced\CsMarketplace\Model\Vorders;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;


/**
 * Class OrderCreditmemoRefund
 * @package Ced\CsMarketplace\Observer
 */
Class OrderCreditmemoRefund implements ObserverInterface
{

    /**
     * @var CollectionFactory
     */
    protected $vOrdersCollectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * OrderCreditmemoRefund constructor.
     * @param CollectionFactory $vOrdersCollectionFactory
     * @param Data $dataHelper
     */
    public function __construct(
        CollectionFactory $vOrdersCollectionFactory,
        Data $dataHelper
    ) {
        $this->vOrdersCollectionFactory = $vOrdersCollectionFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Refund the associated vendor order
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getDataObject();
        try {
            if ($order->getState() == Order::STATE_CLOSED) {
                $vOrders = $this->vOrdersCollectionFactory->create()
                    ->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);

                if (count($vOrders) > 0) {
                    foreach ($vOrders as $vOrder) {
                        if ($vOrder->canCancel()) {
                            $vOrder->setOrderPaymentState(Invoice::STATE_CANCELED);
                            $vOrder->setPaymentState(Vorders::STATE_CANCELED);
                            $vOrder->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->dataHelper->logException($e);
        }
        return $this;
    }
}    
