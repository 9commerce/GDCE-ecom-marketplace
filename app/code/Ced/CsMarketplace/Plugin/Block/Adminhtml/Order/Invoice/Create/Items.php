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

namespace Ced\CsMarketplace\Plugin\Block\Adminhtml\Order\Invoice\Create;

use Ced\CsMarketplace\Helper\InvoiceShipment As InvoiceShipmentHelper;
use Magento\Framework\UrlInterface;

/**
 * Class Items
 * @package Ced\CsMarketplace\Plugin\Block\Adminhtml\Order\Invoice\Create
 */
class Items
{

    /**
     * @var InvoiceShipmentHelper
     */
    private $invoiceShipmentHelper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Items constructor.
     * @param InvoiceShipmentHelper $invoiceShipmentHelper
     * @param UrlInterface $url
     */
    public function __construct(
        InvoiceShipmentHelper $invoiceShipmentHelper,
        UrlInterface $url
    ) {
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
        $this->url = $url;
    }

    /**
     * @param $subject
     * @param $updateUrl
     * @return string
     */
    public function afterGetUpdateUrl($subject, $updateUrl) {
        if ($this->invoiceShipmentHelper->isModuleEnable() && $this->invoiceShipmentHelper->canSeparateInvoiceAndShipment()) {
            $updateUrl = $this->url->getUrl('sales/*/updateQty',['_current' => true]);
        }
        return $updateUrl;
    }
}