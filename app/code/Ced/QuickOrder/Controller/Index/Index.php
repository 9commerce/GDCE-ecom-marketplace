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
 * @package     Ced_QuickOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\QuickOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory; 
use \Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Registry
     */
    public $_coreRegistry;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_customerSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        StoreManagerInterface $storeManager
    ){
        $this->_coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;       
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        parent:: __construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $storeCode = $this->storeManager->getStore()->getCode(); 
        $value = $this->scopeConfig->getValue(
            'quickorder/general/group',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeCode);
        $break_value = explode(',', $value);
        $enabledValue = $this->scopeConfig->getValue(
            'quickorder/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($enabledValue){
            if($this->_customerSession->isLoggedIn()) {
                $customerGroup = $this->_customerSession->getCustomer()->getGroupId();
                    if(in_array($customerGroup,$break_value)){   
                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->getConfig()->getTitle()->set(__('Quick Order'));
                        return $resultPage;
                    }
                    else{
                        $this->messageManager->addError(__('You are not allowed to go through this page'));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath($this->_redirect->getRefererUrl());
                        return $resultRedirect;
                    }
            }
            else{
                $customerGroup = '0';
                if(in_array($customerGroup,$break_value)){   
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(__('Quick Order'));
                    return $resultPage;
                }
                else{
                    $this->messageManager->addError(__('You are not allowed to go through this page'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }

            }
               
        }
        else{
            $this->messageManager->addError(__('You are not allowed to go through this page'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
        
    }
