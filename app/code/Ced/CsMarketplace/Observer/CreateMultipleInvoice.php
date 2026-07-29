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


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ced\CsMarketplace\Helper\InvoiceShipment As InvoiceShipmentHelper;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session As CustomerSession;

/**
 * Class CreateMultipleInvoice
 * @package Ced\CsMarketplace\Observer
 */
Class CreateMultipleInvoice implements ObserverInterface
{
    /**
     * @var InvoiceShipmentHelper
     */
    private $invoiceShipmentHelper;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * CreateMultipleInvoice constructor.
     * @param InvoiceShipmentHelper $invoiceShipmentHelper
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param InvoiceSender $invoiceSender
     * @param LoggerInterface $logger
     * @param CustomerSession $customerSession
     */
    public function __construct(
        InvoiceShipmentHelper $invoiceShipmentHelper,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger,
        CustomerSession $customerSession
    )
    {
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->createMultipleInvoice($order);
    }

    /**
     * @param $order
     * @return bool
     */
    private function createMultipleInvoice($order)
    {
        try{
            if ($this->invoiceShipmentHelper->isModuleEnable() &&
                $this->invoiceShipmentHelper->canSeparateInvoiceAndShipment() &&
                $this->customerSession->getCreateMultipleInvoiceFlag() == true) {
                if ($order && $order->getId()) {
                    $orderId = $order->getId();
                    if ($order->canInvoice() === true) {
                        $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForInvoice($orderId);
                        if (count($vendorIds)) {
                            foreach ($vendorIds as $vendorId) {
                                $invoiceItems = [];
                                if (empty($vendorId)) {
                                    $invoiceItems = $this->invoiceShipmentHelper->getVendorItemsForInvoice($orderId, 0);
                                } elseif ($vendorId > 0) {
                                    $invoiceItems = $this->invoiceShipmentHelper->getVendorItemsForInvoice($orderId, $vendorId);
                                }
                                if (count($invoiceItems)) {
                                    $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
                                    $invoice->register();
                                    $transactionSave = $this->transaction->addObject(
                                        $invoice
                                    )->addObject(
                                        $invoice->getOrder()
                                    );
                                    $transactionSave->save();
                                    $this->invoiceSender->send($invoice);
                                }
                            }
                        }
                    }
                    $this->customerSession->unsCreateMultipleInvoiceFlag();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return true;
    }
}