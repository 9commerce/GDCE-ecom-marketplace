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

namespace Ced\CsMarketplace\Controller;

use Ced\CsMarketplace\Model\Session as MarketplaceSession;
use Ced\CsMarketplace\Model\Vorders as vendorOrder;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Vorders
 * @package Ced\CsMarketplace\Controller
 */
class Vorders extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var MarketplaceSession
     */
    public $marketplacesession;

    /**
     * @var \Magento\Framework\Registry|null
     */
    public $_coreRegistry = null;

    /**
     * @var vendorOrder
     */
    public $_vorders;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Vorders constructor.
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
        vendorOrder $vorders
    ) {
        $this->_vorders = $vorders;
        $this->_coreRegistry = $registry;
        $this->marketplacesession = $mktSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);

    }

    /**
     * Try to load valid order by order_id and register it
     * @param null $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
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

        if ($orderId) {
            $vorder = $this->_vorders->load($orderId);
        } else if ($incrementId) {
            $vendorId = $this->marketplacesession->getVendorId();
            $vorder = $this->_vorders->loadByField(['order_id', 'vendor_id'], [$incrementId, $vendorId]);
        }
        if ($this->_canViewOrder($vorder)) {
            $this->_coreRegistry->register('current_order', $vorder->getOrder());
            $this->_coreRegistry->register('current_vorder', $vorder);
            return true;
        } else {
            $this->_redirect('*/*');
        }
        return false;
    }

    /**
     * Check order view availability
     * @param $vorder
     * @return bool
     */
    protected function _canViewOrder($vorder)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;

        }
        $vendorId = $this->marketplacesession->getVendorId();

        $incrementId = $vorder->getOrder()->getIncrementId();

        $collection = $this->_vorders->create()->getCollection();
        $collection->addFieldToFilter('id', $vorder->getId())
            ->addFieldToFilter('order_id', $incrementId)
            ->addFieldToFilter('vendor_id', $vendorId);

        if (count($collection) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
