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

namespace Ced\CsOrder\Controller\Invoice;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Service\InvoiceService;

class NewAction extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var
     */
    protected $registry;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    public $csorderData;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    public $vordersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $vordersResource;

    /**
     * @var \Ced\CsOrder\Model\Invoice
     */
    public $invoiceFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    public $backendSession;

    /**
     * NewAction constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param InvoiceService $invoiceService
     * @param \Ced\CsOrder\Helper\Data $csorderData
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     * @param \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        InvoiceService $invoiceService,
        \Ced\CsOrder\Helper\Data $csorderData,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource,
        \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->invoiceService = $invoiceService;
        $this->csorderData = $csorderData;
        $this->vordersFactory = $vordersFactory;
        $this->vordersResource = $vordersResource;
        $this->invoiceFactory = $invoiceFactory;
        $this->backendSession = $backendSession;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendorFactory
        );
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|PageFactory
     */
    public function execute()
    {
        $csOrderHelper = $this->csorderData;
        $vendorId = $this->session->getVendorId();
        $vorderId = $this->getRequest()->getParam('vorder_id');
        $vorder = $this->vordersFactory->create();
        $this->vordersResource->load($vorder, $vorderId);

        $invoiceData = $this->getRequest()->getParam('invoice', []);
        $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $vorder->getOrder();

            $this->registry->register("current_vorder", $vorder);
            $this->registry->register("current_order", $order);

            if (!$csOrderHelper->canCreateInvoiceEnabled($vorder)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Not allowed to create Invoice.'));
            }

            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

            $this->invoiceFactory->create()->setVendorId($vendorId)->updateTotal($invoice);//update Invoice total

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->registry->register('current_invoice', $invoice);

            $comment = $this->backendSession->getCommentText(true);
            if ($comment) {
                $invoice->setCommentText($comment);
            }

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage  $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Invoice'));
            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->_redirectToOrder($vorderId, $order->getId());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an invoice.');
            return $this->_redirectToOrder($vorderId, $order->getId());
        }
    }

    /**
     * Redirect to order view page
     *
     * @param  int $vorderId
     * @param  int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($vorderId, $orderId)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('csorder/vorders/view', ['vorder_id' => $vorderId,'order_id' => $orderId]);
        return $resultRedirect;
    }
}
