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

namespace Ced\CsMarketplace\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Ced\CsMarketplace\Helper\InvoiceShipment;
use Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory As VendorCollectionFactory;

/**
 * Class SelectVendor
 * @package Ced\CsMarketplace\Block\Adminhtml
 */
class SelectVendor extends Template
{
    /**
     * @var InvoiceShipment
     */
    private $invoiceShipmentHelper;

    /**
     * @var VendorCollectionFactory
     */
    private $vendorCollectionFactory;

    /**
     * SelectVendor constructor.
     * @param Template\Context $context
     * @param InvoiceShipment $invoiceShipmentHelper
     * @param VendorCollectionFactory $vendorCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        InvoiceShipment $invoiceShipmentHelper,
        VendorCollectionFactory $vendorCollectionFactory,
        array $data = []
    ) {
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendors() {
        $vendors = [];
        $type = $this->getRequest()->getParam('type');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($type == InvoiceShipment::TYPE_INVOICE) {
            $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForInvoice($orderId);
            $urlPath = 'sales/order_invoice/new';
        } else {
            $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForShipment($orderId);
            $urlPath = 'adminhtml/order_shipment/new';
        }
        $associatedVendors = $this->vendorCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['in' => $vendorIds]);
        foreach ($associatedVendors as $vendor) {
            $vendorId = $vendor->getId();
            $data['is_admin'] = false;
            $data['name'] = $vendor->getData('name');
            $data['public_name'] = $vendor->getData('public_name');
            $data['email'] = $vendor->getData('email');
            $data['status'] = ucfirst($vendor->getData('status'));
            $data['row_url'] = $this->getUrl($urlPath, ['order_id' => $orderId, 'vendor_id' => $vendorId]);
            $data['vendor_edit_url'] = $this->getUrl('csmarketplace/vendor/edit', ['vendor_id' => $vendorId]);
            $vendors[] = $data;
        }
        foreach ($vendorIds as $id) {
            if (empty($id)) {
                $vendors[] = [
                    'is_admin' => true,
                    'row_url' => $this->getUrl($urlPath, ['order_id' => $orderId, 'vendor_id' => 0])
                ];
                break;
            }
        }
        return $vendors;
    }
}