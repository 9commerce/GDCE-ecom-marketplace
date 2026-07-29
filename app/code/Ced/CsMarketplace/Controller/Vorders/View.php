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

namespace Ced\CsMarketplace\Controller\Vorders;

use Ced\CsMarketplace\Model\Session as MarketplaceSession;
use Ced\CsMarketplace\Model\Vorders as vendorOrder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class View
 * @package Ced\CsMarketplace\Controller\Vorders
 */
class View extends \Ced\CsMarketplace\Controller\Vorders
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var MarketplaceSession
     */
    protected $mktSession;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $vordersCollection;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
     * @param MarketplaceSession $mktSession
     * @param vendorOrder $vorders
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
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
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection,
        MarketplaceSession $mktSession,
        vendorOrder $vorders,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
    )
    {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->mktSession = $mktSession;
        $this->vordersCollection = $vordersCollection;
        $this->vordersFactory = $vordersFactory;
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor, $mktSession, $vorders);
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        if (!$this->_loadValidOrder()) {
            return false;
        }
        $resultPage = $this->resultPageFactory->create();
        $helper = $this->csmarketplaceHelper;
        $helper->readNotification($this->getRequest()->getParam('order_id', 0));
        $navigationBlock = $resultPage->getLayout()->getBlock('Ced\CsMarketplace\Block\Vendor\Navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('csmarketplace/vorders/');
        }
        return $resultPage;
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param  int $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _loadValidOrder($orderId = null)
    {
        if($orderId === null)
            $orderId = (int)$this->getRequest()->getParam('vorder_id', 0)?(int)$this->getRequest()->getParam('vorder_id', 0):(int)$this->getRequest()->getParam('order_id', 0);

        $incrementId = 0;
        if ($orderId == 0) {
            $incrementId = (int)$this->getRequest()->getParam('increment_id', 0);
            if (!$incrementId) {
                $this->_forward('noRoute');
                return false;
            }
        }

        if ($orderId) {
            $vOrderModel = $this->vordersFactory->create()->load($orderId);
        } elseif ($incrementId) {
            $vendorId = $this->mktSession->getVendorId();
            $vOrderModel = $this->vordersFactory->create()->loadByField(array('order_id', 'vendor_id'),
                [$incrementId, $vendorId]);
        }

        if (!empty($vOrderModel)) {
            $orderModel = $vOrderModel->getOrder();
            if ($this->_canViewOrder($vOrderModel)) {
                $this->registry->register('current_order', $orderModel);
                $this->registry->register('current_vorder', $vOrderModel);
                return true;
            }
        }
        $this->_redirect('*/*');
        return false;
    }

    /**
     * Check order view availability
     * @param $vorder
     * @return bool
     */
    protected function _canViewOrder($vorder)
    {
        if (!$this->_getSession()->getVendorId())
            return false;

        $vendorId = $this->mktSession->getVendorId();
        $incrementId = $vorder->getOrder()->getIncrementId();

        $vOrderCollection = $this->vordersCollection->create();
        $vOrderCollection->addFieldToFilter('id', $vorder->getId())
            ->addFieldToFilter('order_id', $incrementId)
            ->addFieldToFilter('vendor_id', $vendorId);

        return (count($vOrderCollection) > 0) ? true : false;
    }
}
