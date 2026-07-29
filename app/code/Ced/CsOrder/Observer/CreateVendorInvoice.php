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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

class CreateVendorInvoice implements ObserverInterface
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplacehelper;

    /**
     * @var \Ced\CsOrder\Model\InvoiceFactory
     */
    protected $vinvoice;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Invoice
     */
    protected $_vinvoiceResource;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * CreateVendorInvoice constructor.
     * @param \Ced\CsOrder\Model\InvoiceFactory $vinvoice
     * @param \Ced\CsOrder\Model\ResourceModel\Invoice $vinvoiceResource
     * @param \Ced\CsMarketplace\Helper\Data $marketplacehelper
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Ced\CsOrder\Model\InvoiceFactory $vinvoice,
        \Ced\CsOrder\Model\ResourceModel\Invoice $vinvoiceResource,
        \Ced\CsMarketplace\Helper\Data $marketplacehelper,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        Session $customerSession,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->marketplacehelper = $marketplacehelper;
        $this->vinvoice = $vinvoice;
        $this->_vinvoiceResource = $vinvoiceResource;
        $this->vorders = $vorders;
        $this->request = $request;
        $this->session = $customerSession;
    }

    /**
     * Adds catalog categories to top menu
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postItems = $this->request->getPost('invoice');
        $postItems = (isset($postItems['items'])) ? $postItems['items'] : [];

        $requestData = $this->request->getParams();
        if (isset($requestData) && !isset($requestData['invoice_id'])) {
            if (empty($postItems)) {
                $params = $this->request->getParam('items');
                $allItem = [];
                if($params) {
                    $allItem = json_decode($params, true);
                }
                if (!empty($allItem)) {
                    foreach ($allItem as $item) {
                        $postItems[$item['item_id']] = $item['quantity'];
                    }
                } else {
                    $invoiceObj = $observer->getEvent()->getInvoice();
                    $orderObj = $invoiceObj->getOrder();

                    //@codingStandardsIgnoreStart
                    $_sessionVendorId = $this->session->getVendorId();
                    if ($_sessionVendorId) {
                        foreach ($orderObj->getAllVisibleItems() as $item) {
                            $vendorId = $item->getVendorId();
                            if ($_sessionVendorId == $vendorId) {
                                $postItems[$item->getId()] = $item->getQtyOrdered();
                            }
                        }
                    } else {
                        foreach ($orderObj->getAllVisibleItems() as $item) {
                            $postItems[$item->getId()] = $item->getQtyOrdered();
                        }
                    }
                    //@codingStandardsIgnoreEnd
                }
            }

            $invoice = $observer->getInvoice();
            $allItems = $invoice->getAllItems();
            $invoiceVendor = [];

            foreach ($allItems as $item) {
                if (isset($postItems[$item->getOrderItemId()]) && $postItems[$item->getOrderItemId()] > 0) {
                    $vendorId = $item->getVendorId();
                    $invoiceVendor[$vendorId] = $vendorId;
                }
            }
            $this->saveVendorInvoice($invoiceVendor, $invoice);
        }
    }

    private function saveVendorInvoice($invoiceVendor, $invoice)
    {
        foreach ($invoiceVendor as $vendorId) {
            $vInvoice = $this->vinvoice->create();
            try {
                $id = $invoice->getId();
                if ($vorder = $this->vorders->getVorderByInvoice($invoice)) {
                    $vInvoice->setInvoiceId($id);
                    $vInvoice->setVendorId($vendorId);
                    $vInvoice->setInvoiceOrderId($invoice->getOrderId());
                    if ($vInvoice->canInvoiceIncludeShipment($invoice)) {
                        $vInvoice->setShippingCode($vorder->getCode());
                        $vInvoice->setShippingDescription($vorder->getShippingDescription());
                        $vInvoice->setBaseShippingAmount($vorder->getBaseShippingAmount());
                        $vInvoice->setShippingAmount($vorder->getShippingAmount());
                    }
                    $this->_vinvoiceResource->save($vInvoice);
                }
            } catch (\Exception $e) {
                $this->marketplacehelper->logException($e);
            }
        }
    }
}
