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

namespace Ced\CsMarketplace\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class VendorName
 * @package Ced\CsMarketplace\Model\Source
 */
class Payment extends \Magento\Sales\Model\Order\Payment
{
    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    private $customerSession;

    private $invoiceShipmentHelper;

    /**
     * @var CreditmemoManager
     */
    private $creditmemoManager = null;


    public function place()
    {
        $this->_eventManager->dispatch('sales_order_payment_place_start', ['payment' => $this]);
        $order = $this->getOrder();

        $this->setAmountOrdered($order->getTotalDue());
        $this->setBaseAmountOrdered($order->getBaseTotalDue());
        $this->setShippingAmount($order->getShippingAmount());
        $this->setBaseShippingAmount($order->getBaseShippingAmount());

        $methodInstance = $this->getMethodInstance();
        $methodInstance->setStore($order->getStoreId());

        $orderState = Order::STATE_NEW;
        $orderStatus = $methodInstance->getConfigData('order_status');
        $isCustomerNotified = $order->getCustomerNoteNotify();

        // Do order payment validation on payment method level
        $methodInstance->validate();
        $action = $methodInstance->getConfigPaymentAction();

        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
        $helper = $objectManager->create(\Ced\CsMarketplace\Helper\InvoiceShipment::class);
        if ($helper->isModuleEnable() && $helper->canSeparateInvoiceAndShipment()) {
            if ($customerSession->getCreateMultipleInvoiceFlag()) {
                $customerSession->unsCreateMultipleInvoiceFlag();
            }
            $vendorIds = [];
            foreach ($order->getAllItems() as $item) {
                $vendorIds[] = $item->getData('vendor_id');
            }
            if (count(array_unique($vendorIds)) && $action == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
                $action = AbstractMethod::ACTION_AUTHORIZE;
                $customerSession->setCreateMultipleInvoiceFlag(true);
            }
        }

        if ($action) {
            if ($methodInstance->isInitializeNeeded()) {
                $stateObject = new \Magento\Framework\DataObject();
                // For method initialization we have to use original config value for payment action
                $methodInstance->initialize($methodInstance->getConfigData('payment_action'), $stateObject);
                $orderState = $stateObject->getData('state') ?: $orderState;
                $orderStatus = $stateObject->getData('status') ?: $orderStatus;
                $isCustomerNotified = $stateObject->hasData('is_notified')
                    ? $stateObject->getData('is_notified')
                    : $isCustomerNotified;
            } else {
                $orderState = Order::STATE_PROCESSING;
                $this->processAction($action, $order);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            }
        } else {
            $order->setState($orderState)
                ->setStatus($orderStatus);
        }

        $isCustomerNotified = $isCustomerNotified ?: $order->getCustomerNoteNotify();

        if (!array_key_exists($orderStatus, $order->getConfig()->getStateStatuses($orderState))) {
            $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        }

        $this->updateOrder($order, $orderState, $orderStatus, $isCustomerNotified);

        $this->_eventManager->dispatch('sales_order_payment_place_end', ['payment' => $this]);

        return $this;
    }



    /**
     * Get order state resolver instance.
     *
     * @deprecated 101.0.0
     * @return OrderStateResolverInterface
     */
    private function getOrderStateResolver()
    {
        if ($this->orderStateResolver === null) {
            $this->orderStateResolver = ObjectManager::getInstance()->get(OrderStateResolverInterface::class);
        }

        return $this->orderStateResolver;
    }

    /**
     * Set payment parent transaction id and current transaction id if it not set
     *
     * @param Transaction $transaction
     * @return void
     */
    private function setTransactionIdsForRefund(Transaction $transaction)
    {
        if (!$this->getTransactionId()) {
            $this->setTransactionId(
                $this->transactionManager->generateTransactionId(
                    $this,
                    Transaction::TYPE_REFUND,
                    $transaction
                )
            );
        }
        $this->setParentTransactionId($transaction->getTxnId());
    }

    //@codeCoverageIgnoreEnd

    /**
     * Collects order invoices totals by provided keys.
     *
     * Returns result as {key: amount}.
     *
     * @param Order $order
     * @param array $keys
     * @return array
     */
    private function collectTotalAmounts(Order $order, array $keys)
    {
        $result = array_fill_keys($keys, 0.00);
        $invoiceCollection = $order->getInvoiceCollection();
        /** @var Invoice $invoice */
        foreach ($invoiceCollection as $invoice) {
            foreach ($keys as $key) {
                $result[$key] += $invoice->getData($key);
            }
        }

        return $result;
    }
}