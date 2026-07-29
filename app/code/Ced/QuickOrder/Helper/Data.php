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

namespace Ced\QuickOrder\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{  
     /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var registry
     */
    public $registry;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;


     /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Registry $registry,
        StoreManagerInterface $storeManager
    ){
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
    }

     /**
     * @return mixed
     */

    public function quickOrderLink()
    {  
        $session = $this->registry->registry('session');
        $storeCode = $this->storeManager->getStore()->getCode(); 
        $enabledValue = $this->scopeConfig->getValue(
            'quickorder/general/activation'
            , \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $break_value = '';
        $value = $this->scopeConfig->getValue(
            'quickorder/general/group',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeCode
        );  
  
        if ($enabledValue && $value != '') {
            $break_value = explode(',', $value);
            if ($session) {
                $customerGroup = $this->customerSession->getCustomer()->getGroupId();  
                if (in_array($customerGroup, $break_value)) {
                    return 'Quick Order';
                } else {
                    return false;
                }
            } else {
                $customerGroup = '0';
                if (in_array($customerGroup, $break_value)) {   
                    return 'Quick Order';
                } else {
                    return false;
                }
            }      
        } else {
            return false;
        }        
    }
}
