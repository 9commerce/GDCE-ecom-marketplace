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
 * @package     Ced_Gst
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsGst\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var mixed
     */
    protected $_storeId;

    protected \Magento\Framework\ObjectManagerInterface $_objectManager;

    protected \Magento\Framework\App\Helper\Context $_context;

    protected \Magento\Framework\App\Cache\TypeListInterface $_cacheTypeList;

    protected \Magento\Framework\App\Cache\Frontend\Pool $_cacheFrontendPool;

    protected \Magento\Framework\App\ProductMetadataInterface $_productMetadata;

    protected \Magento\Framework\App\Config\ValueInterface $_configValueManager;

    protected \Magento\Framework\DB\Transaction $_transaction;

    protected \Magento\Tax\Model\ClassModelFactory $classModelFactory;

    protected \Ced\CsMarketplace\Model\Vendor $marketplaceVendor;

    protected \Magento\Catalog\Model\Product $catalogProduct;

    protected \Magento\Sales\Model\Order\Address $orderAddress;

    protected \Magento\Framework\Pricing\Helper\Data $pricingHelper;

    protected \Magento\Store\Model\StoreManagerInterface $_storeManager;

    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                                \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Framework\App\ProductMetadataInterface $productMetadata,
                                \Magento\Tax\Model\ClassModelFactory $classModelFactory,
                                \Ced\CsMarketplace\Model\Vendor $marketplaceVendor,
                                \Magento\Sales\Model\Order\Address $orderAddress,
                                \Magento\Catalog\Model\Product $catalogProduct,
                                \Magento\Framework\Pricing\Helper\Data $pricingHelper,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\App\Config\ValueInterface $configValueManager,
                                \Magento\Framework\DB\Transaction $dbTransaction
    ) {

        $this->_objectManager = $objectManager;
        $this->_context = $context;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_request = $request;
        $this->_productMetadata = $productMetadata;
        parent::__construct($context);
        $this->_configValueManager = $configValueManager;
        $this->_transaction = $dbTransaction;
        $this->classModelFactory = $classModelFactory;
        $this->marketplaceVendor = $marketplaceVendor;
        $this->catalogProduct = $catalogProduct;
        $this->orderAddress = $orderAddress;
        $this->pricingHelper = $pricingHelper;
        $this->_storeManager = $storeManager;
    }

    public function moduleEnable()	{
    	return $this->scopeConfig->getValue('cedgst/general/active',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Tax Class Id for Tax
     * @return int
     */
    public function getTaxClassIdForVendor($product,$source,$vendorId)	{
    	$classid = '';
    	if ($product && $source) {
    		$warehouse = $this->getWarehouseForVendor($vendorId);
    		$pid = $product->getEntityId();
    		$rate = (int)$this->gstRate($pid);
    		if ($source == $warehouse) {
    			$collection = $this->classModelFactory->create()->getCollection()
    			->setClassTypeFilter('PRODUCT')
    			->addFieldToFilter('class_name',array('eq' => 'GST'.$rate))->getFirstItem();
    			$classid = $collection['class_id'];
    			return $classid;
    		}else{
    			$collection = $this->classModelFactory->create()->getCollection()
    			->setClassTypeFilter('PRODUCT')
    			->addFieldToFilter('class_name',array('like' => '%IGST'.$rate))->getFirstItem();
    			$classid = $collection['class_id'];


    			return $classid;
    		}
    	}
    }

    public function getTaxClassIdForAdmin($product,$source)	{
    	$classid = '';
    	if ($product && $source) {
    		$warehouse = $this->getWarehouseForAdmin();
    		$pid = $product->getEntityId();
    		$rate = (int)$this->gstRate($pid);
    		if ($source == $warehouse) {
    			$collection = $this->classModelFactory->create()->getCollection()
    			->setClassTypeFilter('PRODUCT')
    			->addFieldToFilter('class_name',array('eq' => 'GST'.$rate))->getFirstItem();
    			$classid = $collection['class_id'];
    			return $classid;
    		}else{
    			$collection = $this->classModelFactory->create()->getCollection()
    			->setClassTypeFilter('PRODUCT')
    			->addFieldToFilter('class_name',array('like' => '%IGST'.$rate))->getFirstItem();
    			$classid = $collection['class_id'];

    			return $classid;
    		}
    	}
    }

    public function getWarehouseForVendor($vendorId)	{
    	$vendor = $this->marketplaceVendor->load($vendorId);
    	return $vendor->getRegionId();
    }

    /**
     * Get Product GST Rate
     * @return int
     */
    public function gstRate($id)	{
    	$product = $this->catalogProduct->load($id);
    	return $product->getGstRate();
    }
    /**
     * Set a specified store ID value
     *
     * @param  int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_storeId) { $storeId = (int)$this->_storeId;
        }
        else { $storeId =  isset($_REQUEST['store'])?(int) $_REQUEST['store']:null;
        }
        return $this->_storeManager->getStore($storeId);
    }

    public function getCustomCSS()
    {
        return $this->scopeConfig->getValue('ced_csmarketplace/vendor/theme_css', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsDashboard()
    {
        return $this->getVendorUrl() == $this->_getUrl('*/*/*')
        ||
        $this->getVendorUrl().'/index' == $this->_getUrl('*/*/*')
        ||
        $this->getVendorUrl().'/index/' == $this->_getUrl('*/*/*')
        ||
        $this->getVendorUrl().'index' == $this->_getUrl('*/*/*')
        ||
        $this->getVendorUrl().'index/' == $this->_getUrl('*/*/*');
    }

    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    public function getMarketplaceVersion()
    {
        return trim((string)$this->getReleaseVersion('Ced_CsMarketplace'));
    }

    public function getReleaseVersion($module)
    {
        $modulePath = $this->moduleRegistry->getPath(self::XML_PATH_INSTALLATED_MODULES, $module);
        $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, "$modulePath/etc/module.xml");
        $source = new \Magento\Framework\Simplexml\Config($filePath);
        if($source->getNode(self::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version) {
            return $source->getNode(self::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version->__toString();
        }
        return false;
    }


    /**
     * Url encode the parameters
     *
     * @param  string | array
     * @return string | array | boolean
     */
    public function prepareParams($data)
    {
        if(!is_array($data) && strlen($data)) {
            return urlencode($data);
        }
        if($data && is_array($data) && count($data)>0) {
            foreach($data as $key=>$value){
                $data[$key] = urlencode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Url decode the parameters
     *
     * @param  string | array
     * @return string | array | boolean
     */
    public function extractParams($data)
    {
        if(!is_array($data) && strlen($data)) {
            return urldecode($data);
        }
        if($data && is_array($data) && count($data)>0) {
            foreach($data as $key=>$value){
                $data[$key] = urldecode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Add params into url string
     *
     * @param  string  $url       (default '')
     * @param  array   $params    (default array())
     * @param  boolean $urlencode (default true)
     * @return string | array
     */
    public function addParams($url = '', $params = array(), $urlencode = true)
    {
        if(count($params)>0) {
            foreach($params as $key=>$value){
                if(parse_url($url, PHP_URL_QUERY)) {
                    if($urlencode) {
                        $url .= '&'.$key.'='.$this->prepareParams($value);
                    }
                    else {
                        $url .= '&'.$key.'='.$value;
                    }
                } else {
                    if($urlencode) {
                        $url .= '?'.$key.'='.$this->prepareParams($value);
                    }
                    else {
                        $url .= '?'.$key.'='.$value;
                    }
                }
            }
        }
        return $url;
    }

    /**
     * Retrieve all the extensions name and version developed by CedCommerce
     *
     * @param  boolean $asString (default false)
     * @return array|string
     */
    public function getCedCommerceExtensions($asString = false)
    {
        if($asString) {
            $cedCommerceModules = '';
        } else {
            $cedCommerceModules = array();
        }
        $allModules = $this->_context->getScopeConfig()->getValue(\Ced\Gst\Model\Feed::XML_PATH_INSTALLATED_MODULES);
        $allModules = json_decode(json_encode($allModules), true);
        foreach($allModules as $name=>$module) {
            $name = trim($name);
            if(preg_match('/ced_/i', $name) && isset($module['release_version'])) {
                if($asString) {
                    $cedCommerceModules .= $name.':'.trim($module['release_version']).'~';
                } else {
                    $cedCommerceModules[$name] = trim($module['release_version']);
                }
            }
        }
        if($asString) { trim($cedCommerceModules, '~');
        }
        return $cedCommerceModules;
    }

    /**
     * Retrieve environment information of magento
     * And installed extensions provided by CedCommerce
     *
     * @return array
     */
    public function getEnvironmentInformation()
    {
        $info = array();
        $info['domain_name'] = $this->_productMetadata->getBaseUrl();
        $info['magento_edition'] = 'default';
        if(method_exists('Mage', 'getEdition')) { $info['magento_edition'] = $this->_productMetadata->getEdition();
        }
        $info['magento_version'] = $this->_productMetadata->getVersion();
        $info['php_version'] = phpversion();
        $info['feed_types'] = $this->getStoreConfig(\Ced\Gst\Model\Feed::XML_FEED_TYPES);
        $info['installed_extensions_by_cedcommerce'] = $this->getCedCommerceExtensions(true);

        return $info;
    }

    /**
     * Retrieve admin interest in current feed type
     *
     * @param  SimpleXMLElement $item
     * @return boolean $isAllowed
     */
    public function isAllowedFeedType(SimpleXMLElement $item)
    {
        $isAllowed = false;
        if(is_array($this->_allowedFeedType) && count($this->_allowedFeedType) >0) {
            $cedModules = $this->getCedCommerceExtensions();
            switch(trim((string)$item->update_type)) {
            case \Ced\Gst\Model\Source\Updates\Type::TYPE_NEW_RELEASE :
            case \Ced\Gst\Model\Source\Updates\Type::TYPE_INSTALLED_UPDATE :
                if (in_array(\Ced\Gst\Model\Source\Updates\Type::TYPE_INSTALLED_UPDATE, $this->_allowedFeedType) && strlen(trim($item->module)) > 0 && isset($cedModules[trim($item->module)]) && version_compare($cedModules[trim($item->module)], trim($item->release_version), '<')===true) {
                    $isAllowed = true;
                    break;
                }
            case \Ced\Gst\Model\Source\Updates\Type::TYPE_UPDATE_RELEASE :
                if(in_array(\Ced\Gst\Model\Source\Updates\Type::TYPE_UPDATE_RELEASE, $this->_allowedFeedType) && strlen(trim($item->module)) > 0) {
                    $isAllowed = true;
                    break;
                }
                if(in_array(\Ced\Gst\Model\Source\Updates\Type::TYPE_NEW_RELEASE, $this->_allowedFeedType)) {
                    $isAllowed = true;
                }
                break;
            case \Ced\Gst\Model\Source\Updates\Type::TYPE_PROMO :
                if(in_array(\Ced\Gst\Model\Source\Updates\Type::TYPE_PROMO, $this->_allowedFeedType)) {
                    $isAllowed = true;
                }
                break;
            case \Ced\Gst\Model\Source\Updates\Type::TYPE_INFO :
                if(in_array(\Ced\Gst\Model\Source\Updates\Type::TYPE_INFO, $this->_allowedFeedType)) {
                    $isAllowed = true;
                }
                break;
            }
        }
        return $isAllowed;
    }

    /**
     * Function for setting Config value of current store
     *
     * @param string $path,
     * @param string $value,
     */
    public function setStoreConfig($path, $value, $storeId=null)
    {
        $store=$this->_storeManager->getStore($storeId);
        $data = [
                    'path' => $path,
                    'scope' =>  'stores',
                    'scope_id' => $storeId,
                    'scope_code' => $store->getCode(),
                    'value' => $value,
                ];
        $this->_configValueManager->addData($data);
        $this->_transaction->addObject($this->_configValueManager);
        $this->_transaction->save();
    }

    /**
     * Function for getting Config value of current store
     *
     * @param string $path,
     */
    public function getStoreConfig($path,$storeId=null)
    {
        $store=$this->_storeManager->getStore($storeId);
        return $this->scopeConfig->getValue($path, 'store', $store->getCode());
    }

    public function getFormattedPrice($price){
    	$formattedPrice = $this->pricingHelper->currency($price, true, false);
    	return $formattedPrice;
    }

    public function getGstinNo(){
    	return $this->scopeConfig->getValue('cedgst/general/gstin_no',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getHsn($id){
    	$product = $this->catalogProduct->load($id);
    	return $product->getHsn();
    }

    public function getShippingSource($shippingId)	{
    	$id = '';
    	$address = $this->orderAddress->load($shippingId);
    	$id = $address->getRegionId();
    	return $id;
    }

    public function getHsnByProductId($pid)	{
    	$hsn = '';
    	$hsn = $this->catalogProduct->load($pid)->getHsn();
    	return $hsn;
    }

    public function getWarehouseForAdmin()	{
    	return $this->scopeConfig->getValue('cedgst/general/warehouse',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function create($class){
    	return $this->_objectManager->create($class);
    }

    public function getGstinForAdmin(){
    	return $this->scopeConfig->getValue('cedgst/general/gstin_no',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getObjectManager($class){
        return $this->_objectManager->get($class);
    }
}
