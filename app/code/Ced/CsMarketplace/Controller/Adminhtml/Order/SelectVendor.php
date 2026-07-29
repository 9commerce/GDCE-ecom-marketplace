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

namespace Ced\CsMarketplace\Controller\Adminhtml\Order;

use Ced\CsMarketplace\Helper\InvoiceShipment as InvoiceShipmentHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class SelectVendor
 * @package Ced\CsMarketplace\Controller\Adminhtml\Order
 */
class SelectVendor extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var InvoiceShipmentHelper
     */
    private $invoiceShipmentHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InvoiceShipmentHelper $invoiceShipmentHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->invoiceShipmentHelper = $invoiceShipmentHelper;
    }

    /**
     * @return PageFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->invoiceShipmentHelper->isModuleEnable()) {
            return $resultRedirect->setPath('admin/dashboard/index');
        }

        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Please select order.'));
            return $resultRedirect->setPath('sales/order/index');
        }

        $type = $this->getRequest()->getParam('type');
        if (!($type == InvoiceShipmentHelper::TYPE_INVOICE || $type == InvoiceShipmentHelper::TYPE_SHIPMENT)) {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }

        if (!($this->invoiceShipmentHelper->canSeparateInvoiceAndShipment())) {
            if ($type == InvoiceShipmentHelper::TYPE_INVOICE) {
                return $resultRedirect->setPath('sales/order_invoice/start', ['order_id' => $orderId]);
            }
            return $resultRedirect->setPath('adminhtml/order_shipment/start', ['order_id' => $orderId]);
        }

        if ($type == InvoiceShipmentHelper::TYPE_INVOICE) {
            $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForInvoice($orderId);
        } else {
            $vendorIds = $this->invoiceShipmentHelper->getAssociatedVendorIdsForShipment($orderId);
        }

        if (count($vendorIds) == 1) {
            if ($type == InvoiceShipmentHelper::TYPE_INVOICE) {
                return $resultRedirect->setPath('sales/order_invoice/start', ['order_id' => $orderId]);
            }
            return $resultRedirect->setPath('adminhtml/order_shipment/start', ['order_id' => $orderId]);
        } elseif (count($vendorIds) > 1) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Select Vendor'));
            return $resultPage;
        }

        $this->messageManager->addErrorMessage(__('Something went wrong.'));
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}