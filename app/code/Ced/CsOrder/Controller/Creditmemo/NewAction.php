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

namespace Ced\CsOrder\Controller\Creditmemo;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelperData;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Ced\CsOrder\Model\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $vordersResource;

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
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Ced\CsOrder\Helper\Data $csorderHelperData
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     * @param \Ced\CsOrder\Model\Creditmemo $creditmemo
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Ced\CsOrder\Helper\Data $csorderHelperData,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource,
        \Ced\CsOrder\Model\Creditmemo $creditmemo,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->registry = $registry;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->resultPageFactory = $resultPageFactory;
        $this->csorderHelperData = $csorderHelperData;
        $this->vorders = $vorders;
        $this->vordersResource = $vordersResource;
        $this->creditmemo = $creditmemo;
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
            $vendor
        );
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     * @return PageFactory
     */
    public function execute()
    {
        $csOrderHelper = $this->csorderHelperData;

        try {
            $vendorId = $this->session->getVendorId();
            $vorderId = $this->getRequest()->getParam('vorder_id');
            $vorder = $this->vorders;
            $this->vordersResource->load($vorder, $vorderId);
            $order = $vorder->getOrder();

            if (!$csOrderHelper->canCreateCreditmemoEnabled($vorder)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Not allowed to create Credit Memo.'));
            }
            $this->creditmemoLoader->setOrderId($order->getId());
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            $this->creditmemo->setVendorId($vendorId)->updateTotal($creditmemo);//update Invoice total

            if ($creditmemo) {
                if ($comment = $this->backendSession->getCommentText(true)) {
                    $creditmemo->setCommentText($comment);
                }
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Credit Memos'));
                if ($creditmemo->getInvoice()) {
                    $resultPage->getConfig()->getTitle()->prepend(
                        __("New Memo for #%1", $creditmemo->getInvoice()->getIncrementId())
                    );
                } else {
                    $resultPage->getConfig()->getTitle()->prepend(__("New Memo"));
                }
                return $resultPage;
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->_redirectToOrder($vorderId, $order->getId());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an Shipment.');
            return $this->_redirectToOrder($vorderId, $order->getId());
        }
    }

    /**
     * Redirect to order view page
     *
     * @param int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($vorderId, $orderId)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(
            '*/vorders/view',
            [
                'vorder_id' => $vorderId,
                'order_id' => $orderId
            ]
        );
        return $resultRedirect;
    }
}
