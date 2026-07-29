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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Quotations;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Assignedview extends \Ced\CsMarketplace\Controller\Vendor
{
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseOrder;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseOrder,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
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

        $this->_custmerSesion = $customerSession;
        $this->_coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->_custmerSesion->isLoggedIn()) {
            $this->_redirect('csmarketplace/account/login');
            return;
        }
        $is_enabled_quotation = $this->scopeConfig->getValue('ced_purchaseorder/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$is_enabled_quotation) {
            $this->_redirect('csmarketplace/vendor/index/');
            return;
        }

        $modeldata = $this->purchaseOrder->create()->load($this->getRequest()->getParam('id'));
        $this->_coreRegistry->register('porequest', $modeldata);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('View Assigned Quotations'));
        $resultPage->getConfig()->getTitle()->prepend(__('View Assigned Quotations'));
        return $resultPage;
    }
}
