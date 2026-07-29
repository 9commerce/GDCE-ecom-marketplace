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

namespace Ced\CsOrder\Controller\Adminhtml\Vendororder;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

class AddComment extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawFactory;

    /**
     * @var OrderCommentSender
     */
    protected $orderCommentSender;

    /**
     * @var \Magento\Framework\View\Element\Context
     */
    protected $elementContext;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $orderResource;

    /**
     * AddComment constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\View\Element\Context $elementContext
     * @param OrderCommentSender $orderCommentSender
     * @param \Magento\Framework\Controller\Result\RawFactory $rawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\Element\Context $elementContext,
        OrderCommentSender $orderCommentSender,
        \Magento\Framework\Controller\Result\RawFactory $rawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
        $this->elementContext = $elementContext;
        $this->orderCommentSender = $orderCommentSender;
        $this->rawFactory = $rawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderResource = $orderResource;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_initOrder();
        $resultJsonFactory = $this->resultJsonFactory->create();
        $resultRawFactory=$this->rawFactory->create();

        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

                $this->orderResource->save($order);

                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSenderComment = $this->orderCommentSender;
                $orderCommentSenderComment->send($order, $notify, $comment);

                $onj = $this->elementContext;
                $block = $onj->getLayout()
                    ->createBlock(\Ced\CsOrder\Block\Order\View\History::class, 'order_history')
                    ->setTemplate('order/view/history.phtml');
                $response = $block->toHtml();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $resultJsonFactory;
                $resultJson->setData($response);
                return $resultJson;
            } else {
                $resultRaw = $resultRawFactory;
                $resultRaw->setContents($response);
                return $resultRaw;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('csorder/*/');
    }

    /**
     * @return bool|\Magento\Sales\Api\Data\OrderInterface
     */
    protected function _initOrder()
    {
        $coreRegistry = $this->registry;
        $vorderId = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->orderRepository->get($vorderId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $coreRegistry->register('sales_order', $order);
        $coreRegistry->register('current_order', $order);
        return $order;
    }
}
