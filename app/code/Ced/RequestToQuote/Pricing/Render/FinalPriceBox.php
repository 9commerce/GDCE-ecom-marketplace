<?php

namespace Ced\RequestToQuote\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Msrp\Pricing\Price\MsrpPrice;


class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{ 
   protected function wrapResult($html)
   {
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
	   	$storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        	$store = $storeManager->getStore();
		$customer_Session=$objectManager->create('\Magento\Customer\Model\Session');
		$scopeconfig=$objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
		if(!$scopeconfig->getValue('requesttoquote_configuration/active/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store->getId())){
		    return parent::wrapResult($html);
		}
		$pricemsg = __('Login to see price.');
		$hideprice=$scopeconfig->getValue('requesttoquote_configuration/active/hideprice',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$store->getId());
		$groups = explode(',',$hideprice ?? '');
		
			if(!$customer_Session->isLoggedIn()){
			    $groupId = '0';
			}
			else{
			    $groupId =  $customer_Session->getCustomer()->getGroupId();
			}
			if ($groupId == 0){
				$url = $this->getUrl('customer/account/login/');
			   return '<div><a href='.$url.'>'.$pricemsg.'</a></div>';
			}
			elseif(!in_array($groupId, $groups)){
				return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
						'data-role="priceBox" ' .
						'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
						'>' . $html . '</div>';
	        }
        
   }

}
