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

class NewAction extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $_vordersResource;

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
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
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
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->csorderHelper = $csorderHelper;
        $this->registry = $registry;
        $this->vorders = $vorders;
        $this->_vordersResource = $vordersResource;
        $this->shipmentLoader = $shipmentLoader;
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
     * Shipment create page
     * @return void
     */
    public function execute()
    {
        $csOrderHelper = $this->csorderHelper;
        try {
            $vorderId = $this->getRequest()->getParam('vorder_id');
            $vorder = $this->vorders;
            $this->_vordersResource->load($vorder, $vorderId);
            $orderId = $vorder->getOrder()->getId();
            $this->getRequest()->setParam('order_id',$orderId);
            if (!$csOrderHelper->canCreateShipmentEnabled($vorder)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Not allowed to create Shipment.'));
            }

            $this->registry->register("current_vorder", $vorder);
            $shipmentLoader = $this->shipmentLoader;
            $shipmentLoader->setOrderId($orderId);
            $shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $shipmentLoader->load();
            if ($shipment) {
                $comment = $this->backendSession->getCommentText(true);
                if ($comment) {
                    $shipment->setCommentText($comment);
                }

                $this->_view->loadLayout();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipments'));
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Shipment'));
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Shipment') . ' # ' . $shipment->getIncrementId());
                return $resultPage;
            } else {
                $this->_redirect('*/vorders/view', [
                    'vorder_id' => $vorderId,
                    'order_id' => $orderId
                ]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->_redirectToOrder($vorderId, $orderId);
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot create an Shipment.');
            $this->_redirectToOrder($vorderId, $orderId);
        }
    }

    /**
     * Redirect to order view page
     *
     * @param int $vorderId
     * @param int $orderId
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function _redirectToOrder($vorderId, $orderId)
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('csorder/vorders/view', [
                    'vorder_id' => $vorderId,
                    'order_id' => $orderId
                ]);
        return $resultRedirect;
    }
}
