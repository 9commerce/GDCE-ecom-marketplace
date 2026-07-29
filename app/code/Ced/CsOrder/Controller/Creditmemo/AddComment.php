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
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Comments;
use Magento\Sales\Block\Adminhtml\Order\Comments\View;

class AddComment extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoCommentSender
     */
    protected $creditmemoCommentSender;

    /**
     * @var PageFactory
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
     * @var \Magento\Framework\View\Element\Context
     */
    protected $elementContext;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment
     */
    protected $commentResource;

    /**
     * AddComment constructor.
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
     * @param \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender $creditmemoCommentSender
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $rawFactory
     * @param \Magento\Framework\View\Element\Context $elementContext
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment $commentResource
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
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender $creditmemoCommentSender,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $rawFactory,
        \Magento\Framework\View\Element\Context $elementContext,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment $commentResource,
        \Ced\CsOrder\Helper\Data $csorderHelper
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoCommentSender = $creditmemoCommentSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $rawFactory;
        $this->elementContext = $elementContext;
        $this->commentResource=$commentResource;
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
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Add comment to creditmemo history
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $creditmemoLoader = $this->creditmemoLoader;
        $creditmemoCommentSender = $this->creditmemoCommentSender;
        $resultJsonFactory = $this->resultJsonFactory;
        $resultRawFactory = $this->resultRawFactory;

        try {
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a comment.')
                );
            }
            $creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $creditmemoLoader->load();
            $comment = $this->csorderHelper->addCreditMemoComment(
                $creditmemo,
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $this->commentResource->save($comment);

            $creditmemoCommentSender->send($creditmemo, !empty($data['is_customer_notified']), $data['comment']);
            $onj = $this->elementContext;
            $block = $onj->getLayout()->createBlock(Comments::class, 'creditmemo_comments');
            $shipmentBlock = $onj->getLayout()->createBlock(View::class, 'order_comments')
                ->setTemplate('order/comments/view.phtml');
            $block->append($shipmentBlock);
            $response = $block->toHtml();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $resultJson = $resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            $resultRaw = $resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
