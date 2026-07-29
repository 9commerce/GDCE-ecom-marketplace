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
 * Class PrintAction
 * @package Ced\CsMarketplace\Controller\Vorders
 */
class PrintAction extends \Ced\CsMarketplace\Controller\Vorders
{
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
     * PrintAction constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param MarketplaceSession $mktSession
     * @param vendorOrder $vorders
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection
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
        MarketplaceSession $mktSession,
        vendorOrder $vorders,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollection,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
    )
    {
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
            return $this->_redirect('*/vorders/index');

        }

        if (!$this->_loadValidOrder()) {
            return $this->_redirect('*/vorders/index');
        }

        $resultPage = $this->resultPageFactory->create();
        return $resultPage->addHandle('print');

    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param  int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
        if (null === $orderId) {
            $orderId = (int)$this->getRequest()->getParam('order_id', 0);
        }

        $incrementId = 0;
        if ($orderId == 0) {
            $incrementId = (int)$this->getRequest()->getParam('increment_id', 0);
            if (!$incrementId) {
                $this->_forward('noRoute');
                return false;
            }
        }

        $vorder = $this->vordersFactory->create();
        if ($orderId) {
            $vorder = $vorder->load($orderId);
        } else if ($incrementId) {
            $vendorId = $this->mktSession->getVendorId();
            $vorder = $vorder->loadByField(
                ['order_id', 'vendor_id'],
                [$incrementId, $vendorId]
            );
        }

        $order = $vorder->getOrder();
        if ($this->_canViewOrder($vorder)) {
            $this->registry->register('current_order', $order);
            $this->registry->register('current_vorder', $vorder);
            return true;
        }

        $this->_redirect('*/*');
        return false;
    }

    /**
     * Check order view availability
     *
     * @param  \Ced\CsMarketplace\Model\Vorders $vorder
     * @return bool
     */
    protected function _canViewOrder($vorder)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }

        $vendorId = $this->mktSession->getVendorId();
        $incrementId = $vorder->getOrder()->getIncrementId();
        $collection = $this->vordersCollection->create();
        $collection->addFieldToFilter('id', $vorder->getId())
            ->addFieldToFilter('order_id', $incrementId)
            ->addFieldToFilter('vendor_id', $vendorId);

        if (count($collection) > 0) {
            return true;
        }
        return false;
    }
}
