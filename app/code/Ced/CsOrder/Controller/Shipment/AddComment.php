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

namespace Ced\CsOrder\Controller\Shipment;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class AddComment extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender
     */
    protected $shipmentCommentSender;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\Context
     */
    protected $elementContext;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment
     */
    protected $_shipmentResource;
    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

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
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender $shipmentCommentSender
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\Element\Context $elementContext
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
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender $shipmentCommentSender,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Element\Context $elementContext,
        \Ced\CsOrder\Helper\Data $csorderHelper
    ) {
        $this->shipment = $shipment;
        $this->_shipmentResource = $shipmentResource;
        $this->shipmentCommentSender = $shipmentCommentSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
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
        return $this->_authorization->isAllowed('Magento_Sales::sales_shipment');
    }

    /**
     * Add comment to creditmemo history
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ShipmentLoader = $this->shipment;
        $ShipmentLoaderCommentSender = $this->shipmentCommentSender;
        $resultJsonFactory = $this->resultJsonFactory;
        $resultRawFactory = $this->resultRawFactory;
        try {
            $this->getRequest()->setParam('shipment_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a comment.')
                );
            }
            $shipmentId = $this->getRequest()->getParam('shipment_id');
            $ShipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $ShipmentLoader->setCreditmemoId($shipmentId);
            $ShipmentLoader->setCreditmemo($this->getRequest()->getParam('shipment'));
            $ShipmentLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $shipment = $this->_shipmentResource->load($ShipmentLoader, $shipmentId);
            $comment = $this->csorderHelper->addShipmentComment(
                $ShipmentLoader,
                $data['comment'],
                isset($data['is_customer_notified']),
                isset($data['is_visible_on_front'])
            );
            $this->_shipmentResource->save($comment);
            $coreRegistry = $this->registry;
            $coreRegistry->register('current_shipment', $ShipmentLoader, true);
            $ShipmentLoaderCommentSender->send(
                $ShipmentLoader,
                !empty($data['is_customer_notified']),
                $data['comment']
            );
            $onj = $this->elementContext;
            $block=$onj->getLayout()->createBlock(
                \Magento\Shipping\Block\Adminhtml\View\Comments::class,
                'shipment_comments'
            );
            $shipment_block = $onj->getLayout()->createBlock(
                \Magento\Sales\Block\Adminhtml\Order\Comments\View::class,
                'order_comments'
            )->setTemplate('order/comments/view.phtml');
            $block->append($shipment_block);
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
