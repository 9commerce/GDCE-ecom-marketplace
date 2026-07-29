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

namespace Ced\CsMarketplace\Helper;

use Ced\CsMarketplace\Model\NotificationFactory;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\VpaymentFactory;
use Ced\CsMarketplace\Model\VproductsFactory;
use Ced\CsMarketplace\Model\Vshop;
use Magento\Catalog\Model\Product\ActionFactory;
use Magento\Catalog\Model\Product\Website;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Indexer\Model\ProcessorFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;


/**
 * Class Data
 * @package Ced\CsMarketplace\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const LOGINACTION = 'login';
    const REGISTERACTION = 'register';
    const VORDER_CREATE = "VORDER_CREATE";
    const VORDER_CANCELED = "VORDER_CANCELED";
    const VORDER_PAYMENT_STATE_CHANGED = "VORDER_PAYMENT_STATE_CHANGED";

    const SALES_ORDER_CREATE = "SALES_ORDER_CREATE";
    const SALES_ORDER_CANCELED = "SALES_ORDER_CANCELED";
    const SALES_ORDER_ITEM = "SALES_ORDER_ITEM";
    const SALES_ORDER_PAYMENT_STATE_CHANGED = "SALES_ORDER_PAYMENT_STATE_CHANGED";

    const VPAYMENT_CREATE = "VPAYMENT_CREATE";
    const VPAYMENT_TOTAL_AMOUNT = "VPAYMENT_TOTAL_AMOUNT";

    const LOGIN_DEFAULT_DESIGN = 'default';
    const LOGIN_ADVANCE_DESIGN = 'new_design';

    /**
     * @var array
     */
    protected $_allowedFeedType = [];

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * @var ValueInterface
     */
    protected $_configValueManager;

    /**
     * @var Transaction
     */
    protected $_transaction;

    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var
     */
    protected $request;

    /**
     * @var ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var ProcessorFactory
     */
    protected $processorFactory;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var Vshop
     */
    protected $vshop;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var StringUtils
     */
    protected $_stringUtils;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ComponentRegistrarInterface
     */
    protected $moduleRegistry;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var NotificationFactory
     */
    protected NotificationFactory $notificationFactory;

    /**
     * @var VpaymentFactory
     */
    protected $_vpaymentFactory;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * Data constructor.
     * @param FilterManager $filterManager
     * @param ComponentRegistrarInterface $moduleRegistry
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     * @param Http $request
     * @param ProductMetadataInterface $productMetadata
     * @param StoreManagerInterface $storeManager
     * @param ValueInterface $value
     * @param Transaction $transaction
     * @param RequestInterface $requestInterface
     * @param State $state
     * @param WebsiteFactory $websiteFactory
     * @param ActionFactory $actionFactory
     * @param ProcessorFactory $processorFactory
     * @param Website $website
     * @param Vshop $vshop
     * @param ResourceConnection $resourceConnection
     * @param DeploymentConfig $deploymentConfig
     * @param NotificationFactory $notificationFactory
     * @param VproductsFactory $vproductsFactory
     * @param VendorFactory $vendorFactory
     * @param StringUtils $stringUtils
     * @param VpaymentFactory $vpaymentFactory
     * @param Store $store
     * @param Context $context
     */
    public function __construct(
        FilterManager $filterManager,
        ComponentRegistrarInterface $moduleRegistry,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        Http $request,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager,
        ValueInterface $value,
        Transaction $transaction,
        RequestInterface $requestInterface,
        State $state,
        WebsiteFactory $websiteFactory,
        ActionFactory $actionFactory,
        ProcessorFactory $processorFactory,
        Website $website,
        Vshop $vshop,
        ResourceConnection $resourceConnection,
        DeploymentConfig $deploymentConfig,
        NotificationFactory $notificationFactory,
        VproductsFactory $vproductsFactory,
        VendorFactory $vendorFactory,
        StringUtils $stringUtils,
        VpaymentFactory $vpaymentFactory,
        Store $store,
        Context $context

    ) {
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_request = $request;
        $this->_productMetadata = $productMetadata;
        $this->_storeManager = $storeManager;
        $this->_scopeConfigManager = $context->getScopeConfig();
        $this->_configValueManager = $value;
        $this->_transaction = $transaction;
        $this->requestInterface = $requestInterface;
        $this->state = $state;
        $this->websiteFactory = $websiteFactory;
        $this->actionFactory = $actionFactory;
        $this->processorFactory = $processorFactory;
        $this->website = $website;
        $this->vshop = $vshop;
        $this->resourceConnection = $resourceConnection;
        $this->deploymentConfig = $deploymentConfig;
        $this->notificationFactory = $notificationFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->moduleRegistry = $moduleRegistry;
        $this->_stringUtils = $stringUtils;
        $this->_vpaymentFactory = $vpaymentFactory;
        $this->filterManager = $filterManager;
        $this->_moduleManager = $context->getModuleManager();
        $this->_store = $store;
        parent::__construct($context);
    }

    /**
     * @return FilterManager
     */
    public function getFilterManager(){
        return $this->filterManager;
    }

    /**
     * @return StringUtils
     */
    public function getStringUtils() {
        return $this->_stringUtils;
    }

    /**
     * @param $trans_id
     * @return mixed
     */
    public function getTransaction($trans_id) {
        $transaction = $this->_vpaymentFactory->create()->load( $trans_id);
        return $transaction;
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
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        $params = $this->_request->getParams();
        if ($this->_storeId) {
            $storeId = (int)$this->_storeId;
        } else {
            $storeId = isset($params['store']) ? (int)$params['store'] : null;
        }
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Set a specified store ID value
     *
     * @param $website
     * @return $this
     */
    public function setWebsiteId($website)
    {
        $this->_websiteId = $website;
        return $this;
    }

    /**
     * Get current website
     *
     * @return StoreInterface|WebsiteInterface
     * @throws LocalizedException
     */
    public function getWebsite()
    {
        $params = $this->_request->getParams();
        if ($this->_websiteId) {
            $_websiteId = (int)$this->_websiteId;
        } else {
            $_websiteId = isset($params['website']) ? (int)$params['website'] : null;
        }
        return $this->_storeManager->getWebsite($_websiteId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCustomCSS()
    {
        return $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/vendor/theme_css',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
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
            $this->getVendorUrl() . '/index' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . '/index/' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . 'index' == $this->_getUrl('*/*/*')
            ||
            $this->getVendorUrl() . 'index/' == $this->_getUrl('*/*/*');
    }

    /**
     * @param $logo_src
     * @param $logo_alt
     * @return $this
     */
    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getLogoSrc()
    {
        $logo_path = $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/vendor/vendor_logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
        return $logo_path;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getLogoAlt()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vendor/vendor_logo_alt',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * @return mixed
     */
    public function getVendorFooterText()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vendor/vendor_footer_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getMarketplaceVersion()
    {
        return trim((string)$this->getReleaseVersion('Ced_CsMarketplace'));
    }

    /**
     * @param $module
     * @return bool|string
     */
    public function getReleaseVersion($module)
    {
        $modulePath = $this->moduleRegistry->getPath(
            \Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES,
            $module
        );
        $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR,
            "$modulePath/etc/module.xml"
        );
        $source = new \Magento\Framework\Simplexml\Config($filePath);
        if ($source->getNode(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES)->attributes()
            ->release_version) {
            return $source->getNode(\Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES)->attributes()
                ->release_version->__toString();
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
        if (!is_array($data) && strlen($data)) {
            return urlencode($data);
        }
        if ($data && is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
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
        if (!is_array($data) && strlen($data)) {
            return urldecode($data);
        }
        if ($data && is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $data[$key] = urldecode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Add params into url string
     *
     * @param  string $url (default '')
     * @param  array $params (default array())
     * @param  boolean $urlencode (default true)
     * @return string | array
     */
    public function addParams($url = '', $params = array(), $urlencode = true)
    {
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (\Laminas\Uri\Http::parse($url)) {
                    if ($urlencode) {
                        $url .= '&' . $key . '=' . $this->prepareParams($value);
                    } else {
                        $url .= '&' . $key . '=' . $value;
                    }
                } else {
                    if ($urlencode) {
                        $url .= '?' . $key . '=' . $this->prepareParams($value);
                    } else {
                        $url .= '?' . $key . '=' . $value;
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
        if ($asString) {
            $cedCommerceModules = '';
        } else {
            $cedCommerceModules = [];
        }
        $allModules = $this->_scopeConfigManager->getValue(
            \Ced\CsMarketplace\Model\Feed::XML_PATH_INSTALLATED_MODULES
        );
        $allModules = json_decode(json_encode($allModules), true);
        foreach ($allModules as $name => $module) {
            $name = trim($name);
            if (preg_match('/ced_/i', $name) && isset($module['release_version'])) {
                if ($asString) {
                    $cedCommerceModules .= $name . ':' . trim($module['release_version']) . '~';
                } else {
                    $cedCommerceModules[$name] = trim($module['release_version']);
                }
            }
        }
        if ($asString) {
            trim($cedCommerceModules, '~');
        }
        return $cedCommerceModules;
    }

    /**
     * Retrieve environment information of magento
     * And installed extensions provided by CedCommerce
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getEnvironmentInformation()
    {
        $info = array();
        $info['domain_name'] = $this->_productMetadata->getBaseUrl();
        $info['magento_edition'] = 'default';
        if (method_exists('Mage', 'getEdition')) {
            $info['magento_edition'] = $this->_productMetadata->getEdition();
        }
        $info['magento_version'] = $this->_productMetadata->getVersion();
        $info['php_version'] = phpversion();
        $info['feed_types'] = $this->getStoreConfig(
            \Ced\CsMarketplace\Model\Feed::XML_FEED_TYPES
        );
        $info['installed_extensions_by_cedcommerce'] = $this->getCedCommerceExtensions(true);

        return $info;
    }

    /**
     * Retrieve vendor account page url
     *
     * @return string
     */
    public function getCsMarketplaceUrl()
    {
        return $this->_getUrl('csmarketplace/vshops');
    }


    /**
     * Retrieve CsMarketplace title
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCsMarketplaceTitle()
    {
        return $this->getStoreConfig('ced_vshops/general/vshoppage_top_title',
            $this->_storeManager->getStore(null)->getId());
    }

    /**
     * Retrieve I am a Vendor title
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getIAmAVendorTitle()
    {
        return $this->getStoreConfig('ced_vshops/general/vshoppage_title');
    }

    /**
     * Check customer account sharing is enabled
     *
     * @return boolean
     */
    public function isSharingEnabled()
    {
        if ($this->scopeConfig->getValue(\Magento\Customer\Model\Config\Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE)
            == \Magento\Customer\Model\Config\Share::SHARE_GLOBAL) {
            return true;
        }
        return false;
    }

    /**
     * get Product limit
     *
     * @return integer
     */
    public function getVendorProductLimit()
    {
        if ($this->requestInterface->getParam('store_switcher', 0))
            return $this->scopeConfig->getValue('ced_vproducts/general/limit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->requestInterface->getParam('store_switcher', 0));
        return $this->scopeConfig->getValue('ced_vproducts/general/limit',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve vendor account page url
     *
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->_getUrl('csmarketplace/vendor');
    }

    /**
     * Authenticate vendor
     *
     * @param  int $customerId
     * @return boolean
     * @throws LocalizedException
     */
    public function authenticate($customerId = 0)
    {
        if ($customerId) {
            $vendor = $this->vendorFactory->create()->loadByCustomerId($customerId);
            if ($vendor && $vendor->getId()) {
                return $this->canShow($vendor);
            }
        }
        return false;
    }

    /**
     * Check if a vendor can be shown
     *
     * @param  Vendor|int $vendor
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canShow($vendor)
    {
        if (is_numeric($vendor)) {
            $vendor = $this->vendorFactory->create()->load($vendor);
        }

        if (!is_object($vendor)) {
            $vendor = $this->vendorFactory->create()->loadByAttribute('shop_url', $vendor);
        }

        if (!$vendor || !$vendor->getId()) {
            return false;
        }

        if (!$vendor->getIsActive()) {
            return false;
        }
        if ($this->state->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            if (!$this->isSharingEnabled() && ($vendor->getWebsiteId() !=
                    $this->_storeManager->getStore()->getWebsiteId())) {
                return false;
            }
        }
        return true;
    }

    /**
     *Rebuild Website Ids
     *
     * @return Data $websiteIds
     */
    public function rebuildWebsites()
    {
        $productIds = [];
        $collection = $this->vproductsFactory->create()->getVendorProducts('', 0)
            ->setOrder('vendor_id', 'ASC');
        foreach ($collection as $row) {
            $productIds[] = $row->getProductId();
        }
        $previousVendorId = 0;
        $vendorWebsiteIds = [];
        $removeWebsiteIds = array_keys($this->websiteFactory->create()->getCollection()->toOptionHash());
        $actionModel = $this->actionFactory->create();
        $this->updateWebsites($productIds, $removeWebsiteIds, 'remove');

        foreach ($collection as $row) {
            if (!$this->canShow($row->getVendorId())) {
                continue;
            }
            $productWebsiteIds = explode(',', $row->getWebsiteIds());
            if (!$previousVendorId || $previousVendorId != $row->getVendorId()) {
                $vendorWebsiteIds = $this->vendorFactory->create()->getWebsiteIds($row->getVendorId());
            }
            $previousVendorId = $row->getVendorId();
            $websiteIds = array_intersect($productWebsiteIds, $vendorWebsiteIds);
            if ($websiteIds) {
                $this->updateWebsites([$row->getProductId()], $websiteIds, 'add');
            }
        }

        $indexCollection = $this->processorFactory->create()->getCollection();
        foreach ($indexCollection as $index) {
            /* @var \Magento\Indexer\Model\Processor $index */
            $index->reindexAll();
        }
        $this->cleanCache();

        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $obj->get('Magento\Framework\App\Config\Element')->saveConfig(
            Vendor::XML_PATH_VENDOR_WEBSITE_SHARE, 0
        );
        return $this;
    }

    /**
     * Clear cache related with product id
     *
     * @return bool
     */
    public function cleanCache()
    {
        $types = array('config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl',
            'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate',
            'config_webservice');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        return true;
    }

    /**
     *update Websites
     *
     * @param $productIds
     * @param $websiteIds
     * @param $type
     * @throws LocalizedException
     */
    public function updateWebsites($productIds, $websiteIds, $type)
    {
        $this->_eventManager->dispatch(
            'catalog_product_website_update_before',
            [
                'website_ids' => $websiteIds,
                'product_ids' => $productIds,
                'action' => $type
            ]
        );

        if ($type == 'add') {
            $this->website->addProducts($websiteIds, $productIds);
        } else if ($type == 'remove') {
            $this->website->removeProducts($websiteIds, $productIds);
        }

        $actionModel = $this->actionFactory->create();
        $actionModel->setData([
                'product_ids' => array_unique($productIds),
                'website_ids' => $websiteIds,
                'action_type' => $type
            ]
        );

        $this->_eventManager->dispatch(
            'catalog_product_website_update',
            [
                'website_ids' => $websiteIds,
                'product_ids' => $productIds,
                'action' => $type
            ]
        );
    }

    /**
     * Get new vendor collection
     *
     * @return \Ced\CsMarketplace\Model\ResourceModel\Vendor\Collection
     */
    public function getNewVendors()
    {
        return $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('status', array('eq' => Vendor::VENDOR_NEW_STATUS));
    }

    /**
     * @return array
     */
    public function getFilterParams()
    {
        return array(
            '_secure' => true,
            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid::VAR_NAME_FILTER =>
                base64_encode('status=' . Vendor::VENDOR_NEW_STATUS),
        );
    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorLogEnabled()
    {
        return $this->getStoreConfig('ced_csmarketplace/vlogs/active',
            $this->getStore()->getId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getRootId()
    {
        return $this->_storeManager->getStore()->getRootCategoryId();
    }

    /**
     * Log Process Data
     * @param $data
     * @param bool $tag
     */
    public function logProcessedData($data, $tag = false)
    {
        if (!$this->isVendorLogEnabled()) {
            return;
        }

        $file = $this->getStoreConfig('ced_vlogs/general/process_file');

        $controller = $this->requestInterface->getControllerName();
        $action = $this->requestInterface->getActionName();
        $router = $this->requestInterface->getRouteName();
        $module = $this->requestInterface->getModuleName();

        $out = '';
        $out .= "<pre>";
        $out .= "Controller: $controller\n";
        $out .= "Action: $action\n";
        $out .= "Router: $router\n";
        $out .= "Module: $module\n";
        foreach (debug_backtrace() as $key => $info) {
            $out .= "#" . $key . " Called " . $info['function'] . " in " .
                $info['file'] . " on line " . $info['line'] . "\n";
            break;
        }
        if ($tag) {
            $out .= "#Tag " . $tag . "\n";
        }
        $out .= "</pre>";
    }

    /**
     * Log Exception
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        if (!$this->isVendorLogEnabled()) {
            return;
        }
        $file = $this->getStoreConfig('ced_vlogs/general/exception_file');
        $this->_logger->critical("\n" . $e->__toString(), [], $file, true);
    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorDebugEnabled()
    {
        $isDebugEnable = (int)$this->getStoreConfig('ced_csmarketplace/vlogs/debug_active');
        $clientIp = $this->_getRequest()->getClientIp();
        $allow = false;

        if ($isDebugEnable) {
            $allow = true;

            // Code copy-pasted from core/helper, isDevAllowed method
            // I cannot use that method because the client ip is not always correct
            // (e.g varnish)
            $allowedIps = $this->getStoreConfig('dev/restrict/allow_ips');
            if ($isDebugEnable && !empty($allowedIps) && !empty($clientIp)) {
                $allowedIps = preg_split('#\s*,\s*#', $allowedIps, null,
                    PREG_SPLIT_NO_EMPTY);
                if (array_search($clientIp, $allowedIps) === false
                    && array_search($this->_request->getHttpHost(), $allowedIps) === false
                ) {
                    $allow = false;
                }
            }
        }
        return $allow;
    }

    /**
     * Check Vendor Log is enabled
     *
     * @param $vendor
     * @return boolean
     * @throws LocalizedException
     */
    public function isShopEnabled($vendor)
    {
        $model = $this->vshop->loadByField(array('vendor_id'), array($vendor->getId()));

        if ($model && $model->getId()) {
            if ($model->getShopDisable() == Vshop::DISABLED) {
                return false;
            }
        }
        return true;
    }

    /**
     * Function for setting Config value of current store
     *
     * @param string $path ,
     * @param string $value ,
     * @param null $storeId
     * @throws NoSuchEntityException
     */
    public function setStoreConfig($path, $value, $storeId = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        $data = [
            'path' => $path,
            'scope' => 'stores',
            'scope_id' => $storeId,
            'scope_code' => $store->getCode(),
            'value' => $value,
        ];
        $this->_configValueManager->addData($data);
        $this->_transaction->addObject($this->_configValueManager);
        $this->_transaction->save();
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }

    /**
     * @param $key
     * @return string
     */
    public function getTableKey($key)
    {
        $tablePrefix = (string)$this->deploymentConfig->get(
            \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
        );

        $exists = $this->resourceConnection->getConnection('core_write')->showTableStatus(
            $tablePrefix . 'permission_variable'
        );
        if ($exists) {
            return $key;
        } else {
            return "{$key}";
        }
    }

    /**
     * @return bool|mixed
     */
    public function getCsMarketplaceLink()
    {
        if ($this->scopeConfig->getValue('ced_csmarketplace/general/activation',\Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig->getValue('ced_vshops/general/vshoppage_top_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    /**
     * @return bool|mixed
     */
    public function getIamaVendorLink()
    {
        if ($this->scopeConfig->getValue('ced_csmarketplace/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->scopeConfig->getValue('ced_vshops/general/vshoppage_title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    /**
     * @param $data
     */
    public function setNotification($data){
        $notification = $this->notificationFactory->create();
        $notification->setData($data)->save();
    }

    /**
     * @param $reference
     */
    public function readNotification($reference){
        $notification = $this->notificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('reference_id',$reference)
            ->getFirstItem();

        if($notification && $notification->getId())
            $notification->setStatus(1)->save();
    }

    /**
     * @param $vendor_id
     */
    public function readAllNotifications($vendor_id){
        $notifications = $this->notificationFactory->create()
            ->getCollection()
            ->updateRecords(['status'=>1], 'vendor_id='.$vendor_id)
        ;
    }

    /**
     *
     */
    public function deleteOldNotifications(){
        $notifications = $this->notificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',1)
            ->walk('delete');
    }

    /**
     * @param $action
     * @param $params
     * @return string
     */
    public function getUrl($action, $params){
        return $this->_getUrl($action,$params);
    }

    /**
     * @return bool
     */
    public function canShowLogin()
    {
        if (strtolower($this->_request->getActionName()) == self::LOGINACTION ||
            strtolower($this->_request->getActionName()) == self::REGISTERACTION)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function canShowHeaderInfo()
    {
        if (strtolower($this->_request->getActionName()) == self::LOGINACTION)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function isSocialLoginEnabled()
    {
        if ($this->_moduleManager->isEnabled('Ced_VendorsocialLogin'))
        {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function newLoginPageEnabled()
    {
        $loginPageDesign = $this->getStoreConfig('ced_csmarketplace/login_page/design',
            $this->_storeManager->getStore(null)->getId());
        if ($loginPageDesign == self::LOGIN_ADVANCE_DESIGN)
            return true;

        return false;
    }

    /**
     * @return bool
     */
    public function isFacebookLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_facebook_link',
            $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/facebook_id',
            $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isTwitterLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_twitter_link',
            $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/twitter_id',
            $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isLinkedinLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_linkedin_link',
            $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getLinkedinId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/linkedin_id',
            $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isInstagramLinkEnabled()
    {
        if ($this->getStoreConfig('ced_csmarketplace/social_links/enable_instagram_link',
            $this->_store->getStoreId())) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getInstagramId()
    {
        return $this->getStoreConfig('ced_csmarketplace/social_links/instagram_id',
            $this->_store->getStoreId());
    }

    /**
     * @return array
     */
    public function getAllowedCategories()
    {
        $allowedCategories = [];
        $categories = $this->scopeConfig->getValue(
            'ced_vproducts/general/category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $allowed_categories = explode(',', $categories??'');

        foreach ($allowed_categories as $categoryPath) {
            $path = explode('/', $categoryPath);
            $allowedCategories = array_unique(array_merge($allowedCategories, $path));
        }
        return $allowedCategories;
    }

    /**
     * @return mixed
     */
    public function canHideVendorDetails(){

        return $this->scopeConfig->getValue('ced_csmarketplace/general/hide_vendor_to_customer',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function canHideCustomerDetails(){

        return $this->scopeConfig->getValue('ced_csmarketplace/general/hide_customer_to_vendor',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

     /**
     * @return mixed
     */
    public function reConfirmationRequired(){

        return $this->scopeConfig->getValue('ced_csmarketplace/vproducts/re_confirmation',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isDetailAllowed()
    {
        return $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/general/display_vendor_detail',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function isVerifyAllowed()
    {
        return $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/vendor/verified_email_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }
}
