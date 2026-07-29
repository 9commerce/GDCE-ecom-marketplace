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

namespace Ced\CsMarketplace\Plugin\Block\Adminhtml\Order;

use Ced\CsMarketplace\Helper\InvoiceShipment As InvoiceShipmentHelper;
use Magento\Framework\App\Request\Http As Request;
use Magento\Framework\UrlInterface;

/**
 * Class View
 * @package Ced\CsMarketplace\Plugin\Block\Adminhtml\Order
 */
class View
{
    const VENDOR_SELECTION_URL = 'csmarketplace/order/selectVendor';

    /**
     * @var InvoiceShipmentHelper
     */
    private $invoiceShipmentHelper;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

    private $currentOrderId;

    /**
     * View constructor.
     * @param InvoiceShipmentHelper $invoiceShipmentHelper
     * @param Request $request
     * @param UrlInterface $url
     */
    public function __construct(
        InvoiceShipmentHelper $invoiceShipmentHelper,
        Request $request,
        UrlInterface $url
    ) {
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
        $this->request = $request;
        $this->url = $url;
    }

    /**
     * @param $subject
     * @param $invoiceUrl
     * @return string
     */
    public function afterGetInvoiceUrl($subject, $invoiceUrl) {
        if ($this->invoiceShipmentHelper->isModuleEnable() && $this->invoiceShipmentHelper->canSeparateInvoiceAndShipment()) {
            if ($orderId = $this->getOrderId()) {
                $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForInvoice($orderId);
                if (count($vendorIds) > 1) {
                    $invoiceUrl = $this->url->getUrl(
                        self::VENDOR_SELECTION_URL,
                        ['_current' => true,
                            'type' => InvoiceShipmentHelper::TYPE_INVOICE
                        ]
                    );
                }
            }
        }
        return $invoiceUrl;
    }

    /**
     * @param $subject
     * @param $shipUrl
     * @return string
     */
    public function afterGetShipUrl($subject, $shipUrl) {
        if ($this->invoiceShipmentHelper->isModuleEnable() && $this->invoiceShipmentHelper->canSeparateInvoiceAndShipment()) {
            if ($orderId = $this->getOrderId()) {
                $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForShipment($orderId);
                if (count($vendorIds) > 1) {
                    $shipUrl = $this->url->getUrl(
                        self::VENDOR_SELECTION_URL,
                        ['_current' => true,
                            'type' => InvoiceShipmentHelper::TYPE_SHIPMENT
                        ]
                    );
                }
            }
        }
        return $shipUrl;
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
}