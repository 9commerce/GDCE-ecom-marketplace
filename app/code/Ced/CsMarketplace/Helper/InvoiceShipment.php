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

namespace Ced\CsMarketplace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory As ItemCollectionFactory;

/**
 * Class InvoiceShipment
 * @package Ced\CsMarketplace\Helper
 */
class InvoiceShipment extends AbstractHelper
{
    const MODULE_ENABLE_CONFIG_PATH = 'ced_csmarketplace/general/activation';
    const SEPARATE_INVOICE_AND_SHIPMENT_CONFIG_PATH = 'ced_vorders/general/separate_invoice_and_shipment';
    const TYPE_INVOICE = 'invoice';
    const TYPE_SHIPMENT = 'shipment';

    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * InvoiceShipment constructor.
     * @param Context $context
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        ItemCollectionFactory $itemCollectionFactory
    )
    {
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($context);
    }
    /**
     * @param $path
     * @return mixed
     */
    private function getConfigValue($path) {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isModuleEnable() {
        return $this->getConfigValue(self::MODULE_ENABLE_CONFIG_PATH);
    }

    /**
     * @return mixed
     */
    public function canSeparateInvoiceAndShipment() {
        return $this->getConfigValue(self::SEPARATE_INVOICE_AND_SHIPMENT_CONFIG_PATH);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getAssociatedVendorIdsForInvoice($orderId) {
        $vendorIds = [];
        if ($orderId) {
            $itemCollection = $this->itemCollectionFactory->create();
            $itemCollection->addFieldToFilter('order_id', $orderId);
            $itemCollection->getSelect()->where('`qty_invoiced` < (`qty_ordered` - `qty_canceled`)');
            $itemCollection->getSelect()->group('vendor_id');
            $itemVendorIds = $itemCollection->getColumnValues('vendor_id');
            if (count($itemVendorIds))
                $vendorIds = $itemVendorIds;
        }
        return $vendorIds;
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getAssociatedVendorIdsForShipment($orderId) {
        $vendorIds = [];
        if ($orderId) {
            $itemCollection = $this->itemCollectionFactory->create();
            $itemCollection->addFieldToFilter('order_id', $orderId);
            $itemCollection->getSelect()->where('`qty_shipped` < (`qty_ordered` - `qty_canceled`)');
            $itemCollection->getSelect()->group('vendor_id');
            $itemVendorIds = $itemCollection->getColumnValues('vendor_id');
            if (count($itemVendorIds))
                $vendorIds = $itemVendorIds;
        }
        return $vendorIds;
    }

    /**
     * @param $orderId
     * @param $vendorId
     * @return array
     */
    public function getVendorItemsForInvoice($orderId, $vendorId) {
        $invoiceItems = [];
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter('order_id', $orderId);
        if ($vendorId == 0) {
            $itemCollection->getSelect()->where('(`vendor_id` IS NULL OR `vendor_id` = "")');
        } elseif ($vendorId > 0) {
            $itemCollection->addFieldToFilter('vendor_id', $vendorId);
        }
        $itemCollection->getSelect()->where('`qty_invoiced` < (`qty_ordered` - `qty_canceled`)');
        foreach ($itemCollection as $item) {
            $qty = (int)($item->getData('qty_ordered') - $item->getData('qty_canceled') - $item->getData('qty_invoiced')) ;
            $invoiceItems[$item->getData('item_id')] = $qty;
        }
        return $invoiceItems;
    }
}