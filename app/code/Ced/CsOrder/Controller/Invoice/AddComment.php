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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class AddComment extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pagePageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoice;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice
     */
    protected $invoiceResource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\Context
     */
    protected $elementContext;
    /**
     * @var \Magento\Sales\Model\Order\Invoice\CommentFactory
     */
    protected $invoiceCommentFactory;
    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender $invoiceCommentSender
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource
     * @param \Magento\Framework\View\Element\Context $elementContext
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory
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
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender $invoiceCommentSender,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource,
        \Magento\Framework\View\Element\Context $elementContext,
        \Ced\CsOrder\Helper\Data $csorderHelper
    ) {
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->forwardFactory = $forwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->invoice = $invoice;
        $this->invoiceResource = $invoiceResource;
        $this->registry = $registry;
        $this->elementContext = $elementContext;
        $this->csorderHelper = $csorderHelper;
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Add comment to creditmemo history
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $invoiceCommentSender = $this->invoiceCommentSender;
        $resultJsonFactory = $this->resultJsonFactory;
        $resultRawFactory = $this->resultRawFactory;
        $resultForwardFactory = $this->forwardFactory;
        $resultPageFactory = $this->resultPageFactory;
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
            }
            $invoice = $this->invoice;
            $this->invoiceResource->load($invoice, $this->getRequest()->getParam('invoice_id'));
            if (!$invoice) {
                /**
                 * @var \Magento\Backend\Model\View\Result\Forward $resultForward
                 */
                $resultForward = $resultForwardFactory->create();
                return $resultForward->forward('noroute');
            }
            $this->csorderHelper->addInvoiceComment(
                $invoice,
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );

            $invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
            $this->invoiceResource->save($invoice);
            $coreRegistry = $this->registry;
            $coreRegistry->register('current_invoice', $invoice, true);

            /**
             * @var \Magento\Backend\Model\View\Result\Page $resultPage
             */
            $resultPage = $resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $onj = $this->elementContext;
            $block=$onj->getLayout()->createBlock(
                \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Comments::class,
                'invoice_comments'
            );
            $shipmentBlock =$onj->getLayout()->createBlock(
                \Magento\Sales\Block\Adminhtml\Order\Comments\View::class,
                'order_comments'
            )->setTemplate('order/comments/view.phtml');
            $block->append($shipmentBlock);
            $response = $shipmentBlock->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Please enter a comment.')];
        }
        if (is_array($response)) {
            /**
             * @var \Magento\Framework\Controller\Result\Json $resultJson
             */
            $resultJson = $resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            /**
             * @var \Magento\Framework\Controller\Result\Raw $resultRaw
             */
            $resultRaw = $resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
