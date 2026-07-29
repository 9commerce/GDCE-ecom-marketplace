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

namespace Ced\CsPurchaseOrder\Controller\Request;

/**
 * Class Negotiation
 * @package Ced\CsPurchaseOrder\Controller\Request
 */
class Negotiation extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_custmerSesion;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder
     */
    protected $purchaseOrder;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseOrderFactory;

    /**
     * Negotiation constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder $purchaseOrder
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseOrderFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder $purchaseOrder,
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseOrderFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_custmerSesion = $session;
        $this->scopeConfig = $scopeConfig;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->_custmerSesion->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        if (!$this->scopeConfig->getValue('ced_purchaseorder/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->_redirect('customer/account');
        }

        if ($this->getRequest()->getParam('requestid')) {
            $purchaseorder = $this->purchaseOrderFactory->create();
            $this->purchaseOrder->load($purchaseorder, $this->getRequest()->getParam('requestid'));
            if (!$purchaseorder->getId() || $purchaseorder->getCustomerId() != $this->_custmerSesion->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('Request Does Not Exist'));
                return $this->_redirect('cspurchaseorder/request/view');
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Request'));
            $resultPage->getConfig()->getTitle()->prepend(__('Negotiation'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('Wrong Request Id'));
            return $this->_redirect('cspurchaseorder/request/view');
        }
    }
}
