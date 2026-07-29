<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Request;

class Editview extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsPurchaseOrder\Model\Purchaseorder
     */
    public $purchaseOrder;

    /**
     * Editview constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ced\CsPurchaseOrder\Model\Purchaseorder $purchaseOrder
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
	    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\CsPurchaseOrder\Model\Purchaseorder $purchaseOrder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
	    $this->scopeConfig = $scopeConfig;
	    $this->purchaseOrder = $purchaseOrder;
    }
    public function execute()
    {
	    if(!$this->scopeConfig->getValue('ced_purchaseorder/general/activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
    		$this->_redirect('customer/account');
    		return;
    		}
    	if($this->getRequest()->getParam('requestid')){
    		
    		$po = $this->purchaseOrder->load($this->getRequest()->getParam('requestid'));
	    	if(!$po->getId()){
	    		$this->messageManager->addErrorMessage(__('Request Does Not Exist'));
	    		$this->_redirect('cspurchaseorder/request/view');
	    		return;
	    	}
    		$resultPage = $this->resultPageFactory->create();
	    	$resultPage->getConfig()->getTitle()->prepend(__('Edit'));
	    	$resultPage->getConfig()->getTitle()->prepend(__('View Request'));
	    	return $resultPage;
    	}
    	else {
    		$this->messageManager->addErrorMessage(__('Wrong Request Id'));
    		$this->_redirect('cspurchaseorder/request/view');
    		return;
    	}
    }
}
