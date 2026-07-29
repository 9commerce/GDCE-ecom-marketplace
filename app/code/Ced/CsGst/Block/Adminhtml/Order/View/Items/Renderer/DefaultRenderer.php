<?php

namespace Ced\CsGst\Block\Adminhtml\Order\View\Items\Renderer;

use Magento\Sales\Model\Order\Item;

class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
   
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsGst\Helper\Data $gstHelper,
        array $data = []
	) { 
		$this->_checkoutHelper = $checkoutHelper;
		$this->_messageHelper = $messageHelper;
        $this->vproductsFactory = $vproductsFactory;
        $this->gstHelper = $gstHelper;
		parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper, $checkoutHelper);
	}
	
    /**
     * @param \Magento\Framework\DataObject|Item $item
     * @param string $column
     * @param null $field
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getColumnHtml(\Magento\Framework\DataObject $item, $column, $field = null)
    {
        $html = '';
        switch ($column) {
            case 'product':
                if ($this->canDisplayContainer()) {
                    $html .= '<div id="' . $this->getHtmlId() . '">';
                }
                $html .= $this->getColumnHtml($item, 'name');
                $html .=$this->getHsnCode($item);
                if ($this->canDisplayContainer()) {
                    $html .= '</div>';
                }
                break;
            case 'status':
                $html = $item->getStatus();
                break;
            case 'price-original':
                $html = $this->displayPriceAttribute('original_price');
                break;
            case 'tax-amount':
                $html = $this->displayPriceAttribute('tax_amount');
                break;
            case 'tax-amount-igst':
            	$html=$this->getIgstRate($item);
            	break;
            	
           case 'tax-amount-cgst':
            	$html=$this->getCgstRate($item);
            	break;
            case 'tax-amount-sgst':
            	$html=$this->getSgstRate($item);
            	break;
            case 'tax-percent':
                $html = $this->displayTaxPercent($item);
                break;
            case 'discont':
                $html = $this->displayPriceAttribute('discount_amount');
                break;
            default:
                $html = parent::getColumnHtml($item, $column, $field);
        }
        return $html;
    }

    public function getWarehouse(){
    	return $this->_scopeConfig->getValue('cedgst/general/warehouse',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getIgstRate($item){
    	$gstRate = '';
    	$igstRate = '';
    	$gstAmt = '';
    	$igstAmt = '';
    	$amt = '';
    	
    	$vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($item->getProductId());
    	if($vendorId){
    		$warehouse = $this->gstHelper->getWarehouseForVendor($vendorId);
    	}else{
    		$warehouse = $this->gstHelper->getWarehouseForAdmin();
    	}
    	
    	if(!$item->getOrder()->getShippingAddress()){
    	
    		$regionSelector = $item->getOrder()->getBillingAddress();
    	}else{
    		$regionSelector = $item->getOrder()->getShippingAddress();
    	}
    	$regionId = $regionSelector->getRegionId();
    	
    	if ($regionId == $warehouse) {
    		return 'N/A';
    	}else{
    		$igstAmt = $this->displayPriceAttribute('tax_amount');
    		$igstRate = $this->displayTaxPercent($item);
    		$html='';
    		$html.=$igstAmt;
    		$html.='<div><strong>Rate</strong>';
    		$html.=$igstRate;
    		$html.='</div>';
    		return $html;
    		       
    	}
    	
    }
    
    public function getCgstRate($item){
    	$gstRate = '';
    	$igstRate = '';
    	$gstAmt = '';
    	$igstAmt = '';
    	$amt = '';
        $vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($item->getProductId());
    	if($vendorId){
    		$warehouse = $this->gstHelper->getWarehouseForVendor($vendorId);
    	}else{
    		$warehouse = $this->gstHelper->getWarehouseForAdmin();
    	}
    	if(!$item->getOrder()->getShippingAddress()){
    	
    		$regionSelector = $item->getOrder()->getBillingAddress();
    	}else{
    		$regionSelector = $item->getOrder()->getShippingAddress();
    	}
    	$regionId = $regionSelector->getRegionId();
    	if ($regionId == $warehouse) {
	    	 if ($this->displayTaxPercent($item) > 0) {
	            $rate = $item->getTaxPercent();
	            $amt = $item->getTaxAmount()/2;
	            $gstAmt = $item->getOrder()->formatPrice($amt);
	            $gstRate = ($rate/2).'%';
	        }else{
	            $gstAmt = 'N/A';
	            $igstAmt = 'N/A';
	        }
	        
	        $html ='';
	        if ($this->displayTaxPercent($item) > 0) {
	        	$html.=$gstAmt;
	        	$html.='<div><strong>Rate</strong>';
	        	$html.=$gstRate;
	        	$html.='</div>';
	        	return $html;
	        }else{
	        	return 'N/A';
	        }
    	}else{
    		return 'N/A';
    	} 
    	
    }
    
    public function getSgstRate($item){
    	
    	$gstRate = '';
    	$igstRate = '';
    	$gstAmt = '';
    	$igstAmt = '';
    	$amt = '';
    	$vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($item->getProductId());
    	if($vendorId){
    		$warehouse = $this->gstHelper->getWarehouseForVendor($vendorId);
    	}else{
    		$warehouse = $this->gstHelper->getWarehouseForAdmin();
    	}
    	if(!$item->getOrder()->getShippingAddress()){
    	
    		$regionSelector = $item->getOrder()->getBillingAddress();
    	}else{
    		$regionSelector = $item->getOrder()->getShippingAddress();
    	}
    	$regionId = $regionSelector->getRegionId();
    	if ($regionId == $warehouse) {
    		if ($this->displayTaxPercent($item) > 0) {
    			$rate = $item->getTaxPercent();
    			$amt = $item->getTaxAmount()/2;
    			$gstAmt = $item->getOrder()->formatPrice($amt);
    			$gstRate = ($rate/2).'%';
    		}else{
    			$gstAmt = 'N/A';
    			$igstAmt = 'N/A';
    		}
    		 
    		
    		$html ='';
    		if ($this->displayTaxPercent($item) > 0) {
    			$html.=$gstAmt;
    			$html.='<div><strong>Rate</strong>';
    			$html.=$gstRate;
    			$html.='</div>';
    			return $html;
    		}else{
    			return 'N/A';
    	
    		}
    	}else{
    		return 'N/A';
    	}
    }
}
