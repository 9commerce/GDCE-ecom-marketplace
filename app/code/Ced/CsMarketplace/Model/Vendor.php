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

namespace Ced\CsMarketplace\Model;

use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Image;
use Magento\Customer\Model\Customer;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Ced\CsMarketplace\Model\VproductsFactory;
use Magento\Eav\Model\Entity\Attribute;
use Ced\CsMarketplace\Model\VordersFactory;
use Ced\CsMarketplace\Model\VsettingsFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;


/**
 * Class Vendor
 * @package Ced\CsMarketplace\Model
 */
class Vendor extends \Ced\CsMarketplace\Model\AbstractModel
{

    /**
     * @var string
     */
    protected $_eventPrefix = 'ced_csmarketplace_vendor';

    /**
     * @var bool
     */
    protected $_dataSaveAllowed = true;

    /**
     * @var bool
     */

    protected $_cacheTag = true;

    /**
     * @var bool
     */
    protected $_customer = false;

    const VENDOR_NEW_STATUS = 'new';
    const VENDOR_APPROVED_STATUS = 'approved';
    const VENDOR_DISAPPROVED_STATUS = 'disapproved';
    const VENDOR_DELETED_STATUS = 'deleted';
    const VENDOR_SHOP_URL_SUFFIX = '.html';
    const DEFAULT_SORT_BY = 'name';
    const XML_PATH_VENDOR_WEBSITE_SHARE = "ced_csmarketplace/vendor/customer_share";

    /**
     * @var null
     */
    public $_vendorstatus = null;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var mixed|null
     */
    protected $seoSuiteDataHelper = null;

    /**
     * @var Url
     */
    protected $urlModal;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Image
     */
    protected $marketplaceImageHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $marketplaceMailHelper;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $urlRewriteHelper;

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $logger;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * @var Attribute
     */
    protected $attributeModal;

    /**
     * @var Vendor\AttributeFactory
     */
    protected $marketplaceAttribute;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vOrdersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vSettingsFactory;

    /**
     * @var System\Config\Source\Paymentmethods
     */
    protected $marketplacePaymentMethod;

    /**
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var ResourceModel\Vpayment\CollectionFactory
     */
    protected $vPaymentCollectionFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_aclHelper;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * Vendor constructor.
     * @param Customer $customer
     * @param Data $dataHelper
     * @param Manager $moduleManager
     * @param MessageManagerInterface $messageManager
     * @param Url $urlModal
     * @param RequestInterface $request
     * @param Image $marketplaceImageHelper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Ced\CsMarketplace\Helper\Mail $marketplaceMailHelper
     * @param \Magento\UrlRewrite\Helper\UrlRewrite $urlRewriteHelper
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Framework\Logger\Monolog $logger
     * @param ManagerInterface $eventManager
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory
     * @param Attribute $attributeModal
     * @param Vendor\AttributeFactory $marketplaceAttribute
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsMarketplace\Model\VordersFactory $vOrdersFactory
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vSettingsFactory
     * @param System\Config\Source\Paymentmethods $marketplacePaymentMethod
     * @param WebsiteFactory $websiteFactory
     * @param ResourceModel\Vpayment\CollectionFactory $vPaymentCollectionFactory
     * @param UploaderFactory $uploaderFactory
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Url $url
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Customer $customer,
        Data $dataHelper,
        Manager $moduleManager,
        MessageManagerInterface $messageManager,
        \Ced\CsMarketplace\Model\Url $urlModal,
        RequestInterface $request,
        Image $marketplaceImageHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Ced\CsMarketplace\Helper\Mail $marketplaceMailHelper,
        \Magento\UrlRewrite\Helper\UrlRewrite $urlRewriteHelper,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Framework\Logger\Monolog $logger,
        ManagerInterface $eventManager,
        VproductsFactory $vProductsFactory,
        Attribute $attributeModal,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $marketplaceAttribute,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        VordersFactory $vOrdersFactory,
        VsettingsFactory $vSettingsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Paymentmethods $marketplacePaymentMethod,
        WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory $vPaymentCollectionFactory,
        UploaderFactory $uploaderFactory,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Url $url,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $data = []
    ) {
        parent::__construct(
            $urlRewriteFactory,
            $storeManager,
            $url,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->customer = $customer;
        $this->_dataHelper = $dataHelper;
        $this->moduleManager = $moduleManager;
        $this->messageManager = $messageManager;
        $this->urlModal = $urlModal;
        $this->request = $request;
        $this->marketplaceImageHelper = $marketplaceImageHelper;
        $this->filesystem = $filesystem;
        $this->marketplaceMailHelper = $marketplaceMailHelper;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteHelper = $urlRewriteHelper;
        $this->storeManager = $storeManager;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->vProductsFactory = $vProductsFactory;
        $this->attributeModal = $attributeModal;
        $this->marketplaceAttribute = $marketplaceAttribute;
        $this->resourceConnection = $resourceConnection;
        $this->vOrdersFactory = $vOrdersFactory;
        $this->vSettingsFactory = $vSettingsFactory;
        $this->marketplacePaymentMethod = $marketplacePaymentMethod;
        $this->websiteFactory = $websiteFactory;
        $this->vPaymentCollectionFactory = $vPaymentCollectionFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->_aclHelper = $aclHelper;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);

        if ($moduleManager->isEnabled('Ced_CsSeoSuite')) {
            $this->seoSuiteDataHelper = ObjectManager::getInstance()->get('Ced\CsSeoSuite\Helper\Data');
        }
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vendor');
    }

    /**
     * Load vendor by customer id
     * @param $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomerId($customerId)
    {
        return $this->loadByAttribute('customer_id', $customerId);
    }

    /**
     * Load vendor by vendor/customer email
     * @param $email
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail($email)
    {
        return $this->loadByAttribute('email', $email);
    }

    /**
     * Set customer
     * @param $customer
     * @return Vendor
     */
    public function setCustomer($customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Get customer
     */
    public function getCustomer()
    {
        if(!$this->_customer && $this->getCustomerId()) {
            $this->_customer = $this->customer->load($this->getCustomerId());
        }
        return $this->_customer;
    }

    /**
     * Check vendor is active|approved
     *
     * @return bool
     */
    public function getIsActive()
    {
        return ($this->getData('status') == self::VENDOR_APPROVED_STATUS) ? true : false;
    }

    /**
     * Get UrlSuffix
     */
    public function getUrlSuffix() {
        $suffix = $this->_dataHelper->getStoreConfig('ced_vseo/general/marketplace_url_suffix');
        return $suffix ? $suffix : self::VENDOR_SHOP_URL_SUFFIX ;
    }

    /**
     * Get Urlpath for vendor shop
     */
    public function getUrlPath() {
        if($this->moduleManager->isEnabled('Ced_CsSeoSuite') && !is_null($this->seoSuiteDataHelper)
            && $this->seoSuiteDataHelper->isEnabled()){
            return $this->_dataHelper->getStoreConfig('ced_vseo/general/marketplace_url_key');
        }
        return 'vendor_shop';
    }

    /**
     * get vendor shop url key
     *
     * @param  string $shop_url
     * @return string
     */
    public function getShopUrlKey($shop_url = '')
    {
        if (strlen($shop_url)) {
            return str_replace($this->getUrlSuffix(), '', trim($shop_url));
        } elseif ($this->getId()) {
            return str_replace($this->getUrlSuffix(), '', trim($this->getShopUrl()));
        } else {
            return $shop_url;
        }
    }

    /**
     * get vendor shop url
     *
     * @return string
     */
    public function getVendorShopUrl()
    {
        $urlpath = $this->getUrlPath();
        if (strlen($urlpath) > 0) {
            $url = $urlpath . '/' . trim($this->getShopUrl()) . $this->getUrlSuffix();
        } else {
            $url = trim($this->getShopUrl()) . $this->getUrlSuffix();
        }
        $url = $this->urlModal->getShopUrl($url);
        return rtrim(trim("{$url}"), '/');
    }

    /**
     * Register a vendor
     * @param array $vendorData
     * @return bool|Vendor
     */
    public function register($vendorData = [])
    {
        $customer = $this->getCustomer();

        if ($customer && isset($vendorData['public_name']) && isset($vendorData['shop_url'])) {

            if ($vendorData && count($vendorData)) {
                $vendorData = array_merge($vendorData, $this->_aclHelper->getDefultAclValues());
            } else {
                $vendorData = $this->_aclHelper->getDefultAclValues();
            }
            $vendorData['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            $vendorData['gender'] = $customer->getGender();
            $vendorData['email'] = $customer->getEmail();
            $vendorData['customer_id'] = $customer->getId();
            $vendorData['created_at'] = date('Y-m-d H:i:s');
            $this->addData($vendorData);

            if ($this->validate(array_keys($vendorData))) {
                $this->setErrors('');
                return $this;
            } else {
                return $this;
            }
        }
        return false;
    }

    /**
     * Processing object before save data
     *
     * @return AbstractModel|Vendor
     */
    public function beforeSave()
    {
        try {
            if (!empty($this->request->getFiles()->toArray())) {
                $uploadedFiles = $this->request->getFiles('vendor');
                $vendorPost = $this->request->getParam('vendor');
                $allowedattributes = [];
                foreach ($uploadedFiles as $type => $value) {
                    if ($value['error'] == 0) {
                        $allowedattributes[] = $type;
                    }
                }
                if (count($allowedattributes) > 0) {
                    $attributes = $this->getVendorAttributes()
                        ->addFieldToFilter('frontend_input', array('image', 'file'))
                        ->addFieldToFilter('attribute_code', $allowedattributes);
                    $images = $this->marketplaceImageHelper->UploadImage($attributes);
                    $this->addData($images);
                }

                $mediaDirectory = $this->filesystem
                    ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $path = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vendor');

                foreach ($uploadedFiles as $key => $value) {
                    if (isset($vendorPost[$key]['delete']) &&
                        $vendorPost[$key]['delete'] == 1 &&
                        isset($uploadedFiles[$key])
                    ) {
                        $this->setData($key, '');
                        $imageName = explode('/', $vendorPost[$key]['value']);
                        $imageName = $imageName[count($imageName) - 1];
                        $file = $path . '/' . $imageName;
                        $this->marketplaceImageHelper->deleteVendorImage($file);
                    }
                }
            }

            $customer = $this->getCustomer();
            if ($customer) {
                $this->addData(array('website_id' => $customer->getWebsiteId()));
            }

            if (!$this->getMassFlag()) {
                $previousStatus = $this->getOrigData('status');
                if (!$previousStatus) {
                    $this->marketplaceMailHelper->sendAccountEmail($this->getStatus(), $this);
                    $this->marketplaceMailHelper->sendAccountEmailToAdmin($this);
                }
                if ($previousStatus != '' && $this->getStatus() != $previousStatus) {
                    $this->vProductsFactory->create()
                        ->changeProductsStatus(array($this->getId()), $this->getStatus());
                }
            }

            if ($this->getData('shop_url')) {
                $shopUrlKey = $this->formatShopUrl($this->getData('shop_url'));
                if (!$this->getId() ||
                    ($this->getId() && $this->getData('shop_url') != $this->getOrigData('shop_url'))
                ) {
                    $shopUrlKey = $this->genrateShopUrl($this->getData());
                    if (strlen($this->getUrlPath()) == 0 &&
                        !is_null($this->seoSuiteDataHelper) &&
                        $this->seoSuiteDataHelper->isEnabled()
                    ) {
                        $seoSuiteModal = ObjectManager::getInstance()->create('Ced\CsSeoSuite\Model\Url');

                        $urlExist = $seoSuiteModal->getCollection()
                            ->addFieldToFilter('request_path', [
                                'eq' => $this->getOrigData('shop_url') . $this->getUrlSuffix()
                            ])
                            ->addFieldToFilter('target_path', [
                                'eq' => 'csmarketplace/vshops/view/shop_url/' . $this->getOrigData('shop_url')
                            ]);

                        $params = array();
                        if (count($urlExist) > 0) {
                            $urlExist = $urlExist->getLastItem();
                            if ($urlExist && $urlExist->getId()) {
                                $params['id'] = $urlExist->getId();
                                $params['is_edit'] = 1;
                            }
                        }

                        $params['name'] = 'Marketplace Shop Page - ' . $shopUrlKey;
                        $params['request_path'] = $shopUrlKey . $this->getUrlSuffix();
                        $params['target_path'] = 'csmarketplace/vshops/view/shop_url/' . $shopUrlKey;
                        $params['description'] = '';
                        $this->saveRewrite($params);
                    }
                }
                $this->setData('shop_url', $shopUrlKey);
            }

            parent::beforeSave();
            return $this;
        } catch (\Exception $e) {
            $this->_dataSaveAllowed = false;
            $this->messageManager->addErrorMessage($e->getMessage());
            return parent::beforeSave();
        }
    }

    /**
     * Genrate the vendor shop url
     * @param array $data
     * @param array $result
     * @param int $count
     * @return bool|string
     */
    public function genrateShopUrl($data = array(), $result = array('success' => 0), $count = 0)
    {
        if (isset($result['success']) && !$result['success']) {
            $shopUrlKey = $this->getUnusedPath($data['shop_url'], $count, $this->getUrlSuffix());
            $tempUrlKey = $data['shop_url'];
            $data['shop_url'] = $shopUrlKey;
            $result = $this->checkAvailability($data);
            if (isset($result['success']) && $result['success']) {
                return $shopUrlKey;
            } else {
                $data['shop_url'] = $tempUrlKey;
                return $this->genrateShopUrl($data, $result, $count + 1);
            }
        } else {
            return false;
        }
    }

    /**
     * Urlrewrite save action
     * @param $data
     * @return bool
     */
    public function saveRewrite($data)
    {
        if ($data) {
            if ($this->_registry->registry('current_urlrewrite'))
                $this->_registry->unregister('current_urlrewrite');

            if ($this->_registry->registry('current_urlrewrite_collection'))
                $this->_registry->unregister('current_urlrewrite_collection');

            $id = isset($data['id']) ? $data['id'] : 0;
            $seoSuiteModal = ObjectManager::getInstance()->create('Ced\CsSeoSuite\Model\Url');
            $this->_registry->register('current_urlrewrite', $seoSuiteModal->load($id));

            if ($this->_registry->registry('current_urlrewrite')->getId()) {
                $urlRewrite = $this->urlRewriteFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('url_rewrite_id', [
                        'in' => $this->_registry->registry('current_urlrewrite')->getUrlRewriteIds()
                    ]);
                $this->_registry->register('current_urlrewrite_collection', $urlRewrite);
            }

            $isEdit = isset($data['is_edit']) ? $data['is_edit'] : 0;
            try {
                $requestPath = $data['request_path'];
                $this->urlRewriteHelper->validateRequestPath($requestPath);

                $urlRewriteIds = [];
                if ($this->_registry->registry('current_urlrewrite_collection') &&
                    count($this->_registry->registry('current_urlrewrite_collection')) > 0
                ) {
                    foreach ($this->_registry->registry('current_urlrewrite_collection') as $model) {
                        $model->setRequestPath($requestPath);
                        if ($isEdit){
                            $model->setTargetPath($data['target_path']);
                        }
                        $model->save();
                        $urlRewriteIds[] = $model->getUrlRewriteId();
                    }
                } else {
                    $stores = $this->storeCollectionFactory->create();
                    foreach ($stores as $store) {
                        if ($store->getId()) {
                            $model = $this->urlRewriteFactory->create();
                            $model->setIsAutogenerated(0)
                                ->setStoreId($store->getId())
                                ->setTargetPath($data['target_path'])
                                ->setDescription($data['description'])
                                ->setRequestPath($requestPath)
                                ->save();
                            $urlRewriteIds[] = $model->getUrlRewriteId();
                            $model->save();
                        }
                    }
                }

                $seoUrlModel = $this->_registry->registry('current_urlrewrite');
                if ($isEdit) {
                    $seoUrlModel->setName($data['name']);
                    $seoUrlModel->setRequestPath($requestPath);
                    $seoUrlModel->setTargetPath($data['target_path']);
                }

                $seoUrlModel->setUrlRewriteIds($urlRewriteIds);
                if (!$seoUrlModel->getId()) {
                    $seoUrlModel->setIsAutogenerated(0)
                        ->setName($data['name'])
                        ->setStoreId(0)
                        ->setTargetPath($data['target_path'])
                        ->setDescription($data['description']);
                }

                $seoUrlModel->setRequestPath($requestPath)->save();
                return true;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check for empty values for provided Attribute Code on each entity
     * @param array $entityIds
     * @param array $values
     * @return bool|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveMassAttribute(array $entityIds, array $values)
    {
        if ($values['code'] == "status") {
            if (!isset($values['code']) || !isset($values['value'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('New values was missing.'));
            }
            if ($this->_massCollection == null) {
                $collection = $this->getResourceCollection()->addAttributeToSelect($values['code'])
                    ->addAttributeToFilter('entity_id', array('in' => $entityIds));
            } else {
                $collection = $this->_massCollection;
            }
            if (count($collection)) {
                $vendorIds = [];
                $this->_massCollection = $collection;

                foreach ($collection as $model) {
                    $vendor = $this->load($model->getId());
                    $vendorstatus = $vendor->getStatus();
                    $vendor->setData($values['code'], $values['value'])->setMassFlag(true);

                    if (!$vendor->validate(array($values['code']))) {
                        if ($vendor->getErrors()) {
                            foreach ($vendor->getErrors() as $error) {
                                $this->messageManager->addError($error);
                            }
                        }
                        continue;
                    }
                    $vendor->save();

                    if ($vendorstatus != '' && $vendor->getStatus() != $vendorstatus) {
                        $vendorIds[] = $vendor->getId();

                        /* dispatch event when vendor's account status is changed */
                        $this->eventManager->dispatch('csmarketplace_vendor_status_changed', ['vendor' => $model]);
                        $this->marketplaceMailHelper->sendAccountEmail($vendor->getStatus(),$vendor);
                    }
                }

                if (count($vendorIds) > 0) {
                    $this->vProductsFactory->create()->changeProductsStatus($vendorIds, $values['value']);
                }
                return true;
            }
            return null;
        } else {
            parent::saveMassAttribute($entityIds, $values);
        }
        return false;
    }

    /**
     * @return $this|void
     * @throws \Exception
     */
    public function delete()
    {
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_before', array('vendor' => $this));
        parent::delete();
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_after', array('vendor' => $this));
    }

    /**
     * Return Entity Type instance
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Return Entity Type ID
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityTypeId()
    {
        $entityTypeId = $this->getData('entity_type_id');
        if (!$entityTypeId) {
            $entityTypeId = $this->getEntityType()->getId();
            $this->setData('entity_type_id', $entityTypeId);
        }
        return $entityTypeId;
    }

    /**
     * Retrieve vendor attributes
     * if $groupId is null - retrieve all vendor attributes
     * @param null $groupId
     * @param bool $skipSuper
     * @param int $storeId
     * @param null $visibility
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributes($groupId = null, $skipSuper = false, $storeId = 0, $visibility = null)
    {
        $typeId = $this->getEntityTypeId();
        $attributes = [];
        if ($groupId) {
            $vendorAttributes = $this->attributeModal->getCollection()
                ->setAttributeGroupFilter($groupId)->load();
            if ($storeId) {
                $vendorAttributes->setStoreId($storeId);
            }

            if ($visibility != null) {
                $vendorAttributes->addFieldToFilter('is_visible', array('gt' => $visibility));
            }

            $this->_eventManager->dispatch(
                'ced_csmarketplace_vendor_group_wise_attributes_load_after', [
                'groupId' => $groupId,
                'vendorattributes' => $vendorAttributes
            ]);

            foreach ($vendorAttributes as $attribute) {
                if ($attribute->getData('entity_type_id') == $typeId &&
                    $attribute->getData('attribute_code') != 'website_id'
                ) {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }

    /**
     * Retrieve All vendor Attributes
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorAttributes()
    {
        return $this->attributeModal
            ->setEntityTypeId($this->getEntityTypeId())
            ->setStoreId(0)
            ->getCollection()
            ->addFieldToFilter('entity_type_id', $this->getEntityTypeId());
    }

    /**
     * Retrieve Frontend vendor Attributes
     * @param int $editable
     * @param string $sort
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFrontendVendorAttributes($editable = 0, $sort = 'ASC')
    {
        return $this->marketplaceAttribute->create()
            ->setStoreId($this->storeManager->getStore(null)->getId())
            ->getCollection()
            ->addFieldToFilter('is_visible', ['eq'=>$editable])
            ->setOrder('sort_order', $sort);
    }

    /**
     * Retrieve vendor Orders
     * @param int $vendorId
     * @return mixed
     */
    public function getAssociatedOrders($vendorId = 0)
    {
        if (!$vendorId && $this->getId()) {
            $vendorId = $this->getId();
        }

        $orderGridTable = $this->resourceConnection->getTableName('sales_order_grid');

        $collection = $this->vOrdersFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId);

        $collection->getSelect()->join($orderGridTable,
            'main_table.order_id = ' . $orderGridTable . ".increment_id" . '',
            array('billing_name',
                'increment_id',
                'status',
                'store_id',
                'store_name',
                'customer_id',
                'base_grand_total',
                'base_total_paid',
                'grand_total',
                'total_paid',
                'base_currency_code',
                'order_currency_code',
                'shipping_name',
                'billing_address',
                'shipping_address',
                'shipping_information',
                'customer_email',
                'customer_group',
                'subtotal',
                'shipping_and_handling',
                'customer_name',
                'payment_method',
                'total_refunded'));

        return $collection;
    }

    /**
     * @param array $groups
     * @param int $vendor_id
     */
    public function savePaymentMethods($groups = [], $vendor_id = 0)
    {
        if (!$vendor_id && $this->getId()) {
            $vendor_id = $this->getId();
        }

        $section = \Ced\CsMarketplace\Model\Vsettings::PAYMENT_SECTION;

        if (strlen($section) > 0 && $vendor_id && count($groups) > 0) {
            foreach ($groups as $code => $values) {
                foreach ($values as $name => $value) {
                    $serialized = 0;
                    $key = strtolower($section . '/' . $code . '/' . $name);
                    if (is_array($value)) {
                        $value = $this->_serializer->serialize($value);
                        $serialized = 1;
                    }

                    $setting = $this->vSettingsFactory->create()
                        ->loadByField(['`key`', '`vendor_id`'], [$key, $vendor_id]);

                    if ($setting && $setting->getId()) {
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    } else {
                        $setting = $this->vSettingsFactory->create();
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * Retrieve vendor Payment Methods
     *
     * @param int $vendorId
     * @return array
     */
    public function getPaymentMethods($vendorId = 0)
    {
        $availableMethods = $this->marketplacePaymentMethod->toOptionArray();
        $methods = [];
        $_objectManager = ObjectManager::getInstance();

        if (count($availableMethods) > 0) {
            foreach ($availableMethods as $method) {
                if (isset($method['value'])) {
                    if ($method['model_class'] == '*') {
                        $payment_class = 'Ced\CsMarketplace\Model\Vendor\Payment\Methods\\';
                        $object = $_objectManager->get($payment_class . ucfirst($method['value']));
                    } else {
                        $model_class = $method['model_class'];
                        $object = $_objectManager->get($model_class);
                    }

                    if (is_object($object)) {
                        $methods[$method['value']] = $object;
                    }
                }
            }
        }

        return $methods;
    }

    /**
     * Retrieve vendor Payment Methods
     *
     * @param  int $vendorId
     * @param bool $all
     * @return array
     */
    public function getPaymentMethodsArray($vendorId = 0, $all = true)
    {
        if (!$vendorId && $this->getId()) {
            $vendorId = $this->getId();
        }

        $methods = $this->getPaymentMethods($vendorId);
        $options = [];
        if ($all) {
            $options[''] = '';
        }

        if (count($methods) > 0) {
            foreach ($methods as $code=>$method) {
                $key = strtolower(\Ced\CsMarketplace\Model\Vsettings::PAYMENT_SECTION.'/'.$method->getCode().'/active');
                $setting = $this->vSettingsFactory->create()->loadByField(['key','vendor_id'], [$key,(int)$vendorId]);
                if ($setting && $setting->getId() &&  $setting->getValue()) {
                    $options[$code] = $method->getLabel('label');
                }
            }
        }

        if ($all) {
            $options['other'] = __('Other');
        }

        return $options;
    }

    /**
     * @param int $vendorId
     * @return mixed
     */
    public function getAssociatedPayments($vendorId = 0)
    {
        if (!$vendorId) {
            $vendorId = $this->getId();
        }

        return $this->vPaymentCollectionFactory->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->setOrder('created_at', 'DESC');
    }

    /**
     * Validate customer attribute values.
     * For existing customer password + confirmation will be validated only when password is set
     * (i.e. its change is requested)
     *
     * @param null $attribute
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function validate($attribute = null)
    {
        $errors = [];
        if ($attribute != null) {
            if (!is_array($attribute)) {
                $attribute = array($attribute);
            }
        }

        $attributes = $this->getVendorAttributes();
        if (is_array($attribute) && count($attribute) > 0) {
            $attributes->addFieldToFilter('attribute_code', array('in'=>$attribute));
        }

        $tmp = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getFrontendInput()=='image' || $attribute->getFrontendInput()=='file') {
                try{
                    if ($this->getData($attribute->getAttributeCode()) == '' && $attribute->getIsRequired())
                        $this->uploaderFactory->create(['fileId' =>
                            "vendor[{$attribute->getAttributeCode()}]"]);
                } catch (\Exception $e){
                    $errors[] = $attribute->getFrontend()->getLabel()." is a required Field";
                }
                continue;
            }

            $tmp[] = [
                'Attribute Label' => $attribute->getFrontend()->getLabel(),
                'Attribute Code' => $attribute->getAttributeCode(),
                'Value'=>$this->getData($attribute->getAttributeCode())
            ];

            $terrors = $this->zendValidate(
                $attribute->getFrontend()->getLabel(),
                $this->getData($attribute->getAttributeCode()),
                $attribute->getFrontend()->getClass(),
                $attribute->getIsRequired()
            );

            foreach($terrors as $terror) {
                $errors[] = $terror;
            }
        }

        if (count($errors) == 0) {
            return true;
        } else {
            $this->setErrors($errors);
        }

        return false;
    }

    /**
     * Extract non editable vendor attribute data
     */
    public function extractNonEditableData()
    {
        if ($this->getId()) {
            $nonEditableAttributes = $this->getFrontendVendorAttributes(0, 'ASC');
            foreach ($nonEditableAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $this->getOrigData($attribute->getAttributeCode()));
            }
            foreach (array('shop_url', 'status', 'group', 'created_at', 'email', 'shop_disable') as $attribute_code) {
                $this->setData($attribute_code, $this->getOrigData($attribute_code));
            }
        }
        return $this;
    }

    /**
     * Retrieve vendor Payments
     *
     * @param  int $vendorId Retrieve payments
     * @return \Ced\CsMarketplace\Model\ResourceModel\Vpayment\Collection
     */
    public function getVendorPayments($vendorId = 0)
    {
        if (!$vendorId) {
            $vendorId = $this->getId();
        }

        $collection = $this->vPaymentCollectionFactory->create()
            ->addFieldToFilter('vendor_id', array('eq' => $vendorId));
        return $collection;
    }

    /**
     * @return string
     */
    public function getDefaultSortBy()
    {
        return self::DEFAULT_SORT_BY;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = array(
            self::DEFAULT_SORT_BY => __('Name')
        );
        return $options;
    }

    /**
     *Retrieve Website Ids
     *
     * @param  $vendor
     * @return array $websiteIds
     */
    public function getWebsiteIds($vendor = null)
    {
        if (!$vendor && $this->getId()) {
            $vendor = $this;
        }

        if (is_numeric($vendor)) {
            $vendor = $this->load($vendor);
        }

        if ($vendor && $vendor->getId()) {

            if ($this->_dataHelper->isSharingEnabled()) {
                return array_keys($this->websiteFactory->create()->getCollection()->toOptionHash());
            } else {
                return array($vendor->getWebsiteId());
            }
        }
        return [];
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteFromGroup()
    {
        $this->_getResource()->deleteFromGroup($this);
        return $this;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function groupVendorExists()
    {
        $result = $this->_getResource()->groupVendorExists($this);
        return (is_array($result) && count($result) > 0) ? true : false;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function add()
    {
        $this->_getResource()->add($this);
        return $this;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorExists()
    {
        $result = $this->_getResource()->vendorExists($this);
        return (is_array($result) && count($result) > 0) ? true : false;
    }

    /**
     * Get vendor ACL group
     *
     * @return string
     */
    public function getAclGroup()
    {
        return 'U' . $this->getId();
    }
}
