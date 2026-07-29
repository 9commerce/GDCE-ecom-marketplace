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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vOrdersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $vOrdersResource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * View constructor.
     * @param \Ced\CsMarketplace\Model\VordersFactory $vOrdersFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vOrdersResource
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VordersFactory $vOrdersFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vOrdersResource,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->vOrdersFactory = $vOrdersFactory;
        $this->vOrdersResource = $vOrdersResource;
        $this->session = $session;
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param null $orderId
     * @param bool $viewMode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _loadValidOrder($orderId = null, $viewMode = false)
    {
        $register = $this->registry;

        if (null === $orderId) {
            $orderId = (int) $this->getRequest()->getParam('vorder_id', 0);
        }
        $incrementId = 0;
        if ($orderId == 0) {
            $incrementId = (int) $this->getRequest()->getParam('increment_id', 0);
            if (!$incrementId) {
                $this->_forward('noRoute');
                return false;
            }
        }

        if ($orderId) {
            $vorder = $this->vOrdersFactory->create();
            $this->vOrdersResource->load($vorder, $orderId);
        } elseif ($incrementId) {
            $vendorId = $this->session->getVendorId();
            $vorder = $this->vOrdersFactory->create()->loadByField(['order_id','vendor_id'], [$incrementId,$vendorId]);
        }

        //add view mode for shipping method
        $order = $vorder->getOrder(false, $viewMode);

        $register->register('current_order', $order);
        $register->register('sales_order', $order);
        $register->register('current_vorder', $vorder);
        return true;
    }

    /**
     * @return false|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $register = $this->registry;
        if (!$this->_loadValidOrder(null, true)) {
            return false;
        }
        $order = $register->registry('current_order');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_CsMarketplace::csmarketplace');
        $resultPage->addBreadcrumb(__('CsMarketplace'), __('CsOrder'));
        $resultPage->addBreadcrumb(__('Vendor Order'), __('Vendor Order'));
        $resultPage->getConfig()->getTitle()->prepend(__('Order #' . $order->getIncrementId()));
        return $resultPage;
    }
}
