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

namespace Ced\CsMarketplace\Plugin\Model\Service;

use Ced\CsMarketplace\Helper\InvoiceShipment As InvoiceShipmentHelper;
use Magento\Framework\App\Request\Http As Request;
use Magento\Sales\Model\Order;

/**
 * Class InvoiceService
 * @package Ced\CsMarketplace\Plugin\Model\Service
 */
class InvoiceService
{
    /**
     * @var InvoiceShipmentHelper
     */
    private $invoiceShipmentHelper;

    /**
     * @var Request
     */
    private $request;

    private $currentOrderId;

    private $currentVendorId;

    /**
     * View constructor.
     * @param InvoiceShipmentHelper $invoiceShipmentHelper
     * @param Request $request
     */
    public function __construct(
        InvoiceShipmentHelper $invoiceShipmentHelper,
        Request $request
    ) {
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    public function beforePrepareInvoice($subject, Order $order, array $orderItemsQtyToInvoice = []) {
        if ($this->invoiceShipmentHelper->isModuleEnable() && $this->invoiceShipmentHelper->canSeparateInvoiceAndShipment()) {
            if ($orderId = $this->getOrderId()) {
                $vendorId = $this->getVendorId();
                $invoice = $this->request->getParam('invoice');
                if ($invoice) {
                    $orderItemsQtyToInvoice = $invoice['items'];
                } elseif ($vendorId >= 0) {
                    $orderItemsQtyToInvoice = $this->invoiceShipmentHelper->getVendorItemsForInvoice($orderId, $vendorId);
                }
            }
        }
        return [$order, $orderItemsQtyToInvoice];
    }

    /**
     * @return mixed
     */
    private function getOrderId() {
        if (!$this->currentOrderId) {
            $this->currentOrderId = $this->request->getParam('order_id');
        }
        return $this->currentOrderId;
    }

    /**
     * @return mixed
     */
    private function getVendorId() {
        if (!$this->currentVendorId) {
            $this->currentVendorId = $this->request->getParam('vendor_id');
        }
        return $this->currentVendorId;
    }
}