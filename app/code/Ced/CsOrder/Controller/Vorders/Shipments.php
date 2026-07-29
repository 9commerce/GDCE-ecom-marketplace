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

namespace Ced\CsOrder\Controller\Vorders;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Shipments extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $_vordersResource;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * Shipments constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
    ) {
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
        $this->_vordersResource = $vordersResource;
        $this->registry = $registry;
        $this->vorders = $vorders;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $orderID = (int)$this->getRequest()->getParam('order_id', 0);
        $incrementId = 0;
        if ($orderID == 0) {
            $incrementId = (int)$this->getRequest()->getParam('increment_id', 0);
            if (!$incrementId) {
                $this->_forward('noRoute');
                return;
            }
        }
        $register = $this->registry;
        if ($orderID) {
            $vorder = $this->vorders;
            $this->_vordersResource->load($vorder, $orderID);
        } elseif ($incrementId) {
            $vendorId = $this->customerSession->getVendorId();
            $vorder = $this->vorders->loadByField(['order_id', 'vendor_id'], [$incrementId, $vendorId]);
        }
        $order = $vorder->getOrder(false, false);
        $register->register('current_order', $order);
        $register->register('current_vorder', $vorder);
        $register->register('sales_order', $order);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
