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

use Ced\CsMarketplace\Helper\Mail;
use Ced\CsMarketplace\Helper\Vproducts\Image;
use Ced\CsMarketplace\Helper\Vproducts\Link;
use Magento\Catalog\Model\ConfigFactory;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;


/**
 * Class Vproducts
 * @package Ced\CsMarketplace\Model
 */
class Vproducts extends \Ced\CsMarketplace\Model\FlatAbstractModel
{

    const NOT_APPROVED_STATUS = 0;
    const APPROVED_STATUS = 1;
    const PENDING_STATUS = 2;
    const DELETED_STATUS = 3;

    const ERROR_IN_PRODUCT_SAVE = "error";

    const NEW_PRODUCT_MODE = 'new';
    const EDIT_PRODUCT_MODE = 'edit';

    const AREA_FRONTEND = "frontend";

    /**
     * @var array
     */
    protected $_vproducts = [];

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_registry = null;

    /**
     * @var \Ced\CsMarketplace\Model\Session|null
     */
    protected $_customerSession = null;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_marketplaceDataHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $_vendorModal;

    /**
     * @var Vproducts\StatusFactory
     */
    protected $_vProductStatusFactory;

    /**
     * @var Link
     */
    protected $_marketplaceLinkHelper;

    /**
     * @var Image
     */
    protected $_marketplaceImageHelper;

    /**
     * @var Mail
     */
    protected $_marketplaceMailHelper;

    /**
     * @var Action
     */
    protected $_action;

    /**
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var ProductFactory
     */
    protected $_productResourceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $_stockItemInterfaceFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var ManagerInterface
     */
    protected $_manager;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Vproducts constructor.
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param Vproducts\StatusFactory $vProductStatusFactory
     * @param \Ced\CsMarketplace\Model\Vendor $vendorModal
     * @param Link $marketplaceLinkHelper
     * @param Image $marketplaceImageHelper
     * @param Mail $marketplaceMailHelper
     * @param \Ced\CsMarketplace\Model\Session $_customerSession
     * @param Action $action
     * @param ConfigFactory $configFactory
     * @param ProductFactory $productResourceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StockItemInterfaceFactory $stockItemInterfaceFactory
     * @param Manager $moduleManager
     * @param ManagerInterface $manager
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Ced\CsMarketplace\Model\Vproducts\StatusFactory $vProductStatusFactory,
        Vendor $vendorModal,
        Link $marketplaceLinkHelper,
        Image $marketplaceImageHelper,
        Mail $marketplaceMailHelper,
        Session $_customerSession,
        Action $action,
        ConfigFactory $configFactory,
        ProductFactory $productResourceFactory,
        DataObjectHelper $dataObjectHelper,
        StockItemInterfaceFactory $stockItemInterfaceFactory,
        Manager $moduleManager,
        ManagerInterface $manager,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_marketplaceDataHelper = $marketplaceDataHelper;
        $this->_vendorModal = $vendorModal;
        $this->_vProductStatusFactory = $vProductStatusFactory;
        $this->_marketplaceLinkHelper = $marketplaceLinkHelper;
        $this->_marketplaceImageHelper = $marketplaceImageHelper;
        $this->_marketplaceMailHelper = $marketplaceMailHelper;
        $this->_customerSession = $_customerSession;
        $this->_action = $action;
        $this->_configFactory = $configFactory;
        $this->_productResourceFactory = $productResourceFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_stockItemInterfaceFactory = $stockItemInterfaceFactory;
        $this->_moduleManager = $moduleManager;
        $this->_manager = $manager;
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_resourceConnection = $resourceConnection;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize vproducts model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vproducts');
    }

    /**
     * @return mixed
     */
    public function isProductUpdateApprovalRequired()
    {
        $storeManager = $this->_storeManager;
        $scopeConfig = $this->_scopeConfig;

        return $scopeConfig->getValue('ced_vproducts/general/update_confirmation', 'store',
            $storeManager->getStore()->getCode());
    }

    /**
     * Filter options
     */
    public function getOptionArray()
    {
        return [
            self::APPROVED_STATUS => __('Approved'),
            self::PENDING_STATUS => __('Pending'),
            self::NOT_APPROVED_STATUS => __('Disapproved')
        ];
    }

    /**
     * Filter options
     */
    public function getVendorOptionArray()
    {
        return [
            self::APPROVED_STATUS . Status::STATUS_ENABLED => __('Approved (Enabled)'),
            self::APPROVED_STATUS . Status::STATUS_DISABLED => __('Approved (Disabled)'),
            self::PENDING_STATUS => __('Pending'),
            self::NOT_APPROVED_STATUS => __('Disapproved')
        ];
    }

    /**
     * Mass action options
     */
    public function getMassActionArray()
    {
        return [
            self::APPROVED_STATUS => __('Approved'),
            self::NOT_APPROVED_STATUS => __('Disapproved')
        ];
    }

    /**
     * Get Vendor Id by Product|Product Id
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorIdByProduct($product)
    {
        $vproduct = false;

        if (is_numeric($product)) {
            $vproduct = $this->loadByField('product_id', $product);
        } elseif ($product && $product->getId()) {
            $vproduct = $this->loadByField('product_id', $product->getId());
        }

        if ($vproduct && $vproduct->getId())
            return $vproduct->getVendorId();

        return false;
    }

    /**
     * Validate csmarketplace product attribute values.
     * @return array|bool
     * @throws \Zend_Validate_Exception
     */
    public function validate()
    {
        $errors = [];

        if (!\Zend_Validate::is(trim($this->getName()), 'NotEmpty')) {
            $errors[] = __('The Product Name cannot be empty');
        }
        if (!\Zend_Validate::is(trim($this->getSku()), 'NotEmpty')) {
            $errors[] = __('The Product SKU cannot be empty');
        }

        if ($this->getType() == Type::TYPE_SIMPLE) {
            $weight = trim($this->getWeight());
            if (!\Zend_Validate::is(trim($this->getName()), 'NotEmpty')) {
                $errors[] = __('The Product Weight cannot be empty');
            } else if (!is_numeric($weight) && !($weight > 0)) {
                $errors[] = __('The Product Weight must be 0 or Greater');
            }
        }

        $qty = trim($this->getQty());
        if (!\Zend_Validate::is($qty, 'NotEmpty')) {
            $errors[] = __('The Product Stock cannot be empty');
        } else if (!is_numeric($qty)) {
            $errors[] = __('The Product Stock must be a valid Number');
        }

        if (!\Zend_Validate::is(trim($this->getTaxClassId()), 'NotEmpty')) {
            $errors[] = __('The Product Tax Class cannot be empty');
        }

        $price = trim($this->getPrice());
        if (!\Zend_Validate::is($price, 'NotEmpty')) {
            $errors[] = __('The Product Price cannot be empty');
        } else if (!is_numeric($price) && !($price > 0)) {
            $errors[] = __('The Product Price must be 0 or Greater');
        }

        $special_price = trim($this->getSpecialPrice());
        if ($special_price != '') {
            if (!is_numeric($special_price) && !($special_price > 0)) {
                $errors[] = __('The Product Special Price must be 0 or Greater');
            }
        }


        return (empty($errors) ? true : $errors);
    }

    /**
     * Save Product
     *
     * @param $mode
     * @return Vproducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveProduct($mode)
    {
        $product = $this->getProductData();
        $productData = $this->_request->getParams();
        $productId = $product->getId();

        /**
         * Save Stock data
         *
         * @params int $productId,array $stockdata
         */
        $product = $this->saveStockData($product, $product->getStockData());

        /**
         * Relate Product data
         *
         * @params int mode,int $productId,array $productData
         */
        $this->processPostSave($mode, $product, $productData);

        /**
         * Save Product Images
         *
         * @params int $productId,array $productData
         */
        $this->_marketplaceImageHelper->saveImages($product, $productData);

        /**
         * Save Product Type Specific data
         *
         * @params int $productId,array $productData
         */
        $this->saveTypeData($product, $productData);

        /**
         * Send Product Mails
         *
         * @params array productid,int $status
         */
        if (!$this->isProductApprovalRequired() && $mode == self::NEW_PRODUCT_MODE) {
            $this->_marketplaceMailHelper->sendProductNotificationEmail(array($productId), self::APPROVED_STATUS);
        }

        return $this;
    }

    /**
     * Save Product Stock data
     *
     * @param $product
     * @param $stockData
     * @return int product id
     */
    private function saveStockData($product, $stockData)
    {
        if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')) {
            if (!is_array($stockData)) {
                $stockData = [];
            }

            $stockData['is_in_stock'] = isset($stockData['is_in_stock']) ? $stockData['is_in_stock'] : 1;
            $stockData['qty'] = isset($stockData['qty']) ? $stockData['qty'] : (int)$this->getQty();
            $stockData['is_in_stock'] = isset($stockData['is_in_stock']) ? $stockData['is_in_stock'] : 1;
            $stockData['use_config_manage_stock'] =
                isset($stockData['use_config_manage_stock']) ? $stockData['use_config_manage_stock'] : 1;
            $stockData['is_decimal_divided'] =
                isset($stockData['is_decimal_divided']) ? $stockData['is_decimal_divided'] : 0;

            $stockItem = $this->_stockItemInterfaceFactory->create();
            $this->_dataObjectHelper->populateWithArray(
                $stockItem,
                $stockData,
                '\Magento\CatalogInventory\Api\Data\StockItemInterface'
            );
            $stockItem->setProduct($product);
            $product->setStockItem($stockItem);
        }

        return $product;
    }

    /**
     * Relate Product Data
     *
     * @param $mode
     * @param $product
     * @param $productData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processPostSave($mode, $product, $productData)
    {
        if (isset($productData['product']['website_ids'])) {
            $websiteIds = implode(",", $productData['product']['website_ids']);
        } else {
            $websiteIds = implode(",", $product->getWebsiteIds());
        }

        $productId = $product->getId();
        $storeId = $this->getStoreId();

        switch ($mode) {
            case self::NEW_PRODUCT_MODE:
                $prodata = isset($productData['product']) ? $productData['product'] : [];
                $this->setData($prodata)
                    ->setQty(isset($productData['product']['stock_data']['qty']) ?
                        $productData['product']['stock_data']['qty'] : 0)
                    ->setIsInStock(isset($productData['product']['stock_data']['is_in_stock']) ?
                        $productData['product']['stock_data']['is_in_stock'] : 1)
                    ->setPrice($product->getPrice())
                    ->setSpecialPrice($product->getSpecialPrice())
                    ->setCheckStatus($this->isProductApprovalRequired() ? self::PENDING_STATUS : self::APPROVED_STATUS)
                    ->setProductId($productId)
                    ->setVendorId($this->_customerSession->getVendorId())
                    ->setType(isset($productData['type']) ? $productData['type'] : Type::DEFAULT_TYPE)
                    ->setWebsiteId($websiteIds)
                    ->setStatus($this->isProductApprovalRequired() ? Status::STATUS_DISABLED : Status::STATUS_ENABLED)
                    ->save();
                break;

            case self::EDIT_PRODUCT_MODE:
                $model = $this->loadByField(array('product_id'), array($product->getId()));

                if ($model && $model->getId()) {
                    $model->addData(isset($productData['product']) ? $productData['product'] : []);
                    $model->addData(isset($productData['product']['stock_data']) ?
                        $productData['product']['stock_data'] : []);
                    $model->addData([
                        'store_id' => $storeId,
                        'website_ids' => $websiteIds,
                        'price' => $product->getPrice(),
                        'special_price' => $product->getSpecialPrice()
                    ]);
                    $model->setCheckStatus(($this->isProductApprovalRequired() && $this->isProductApprovalRequiredAfterEdit()) ? self::PENDING_STATUS : $model->getCheckStatus());
                    if (isset($productData['product']['status'])) {
                        $model->setStatus(($this->isProductApprovalRequired() && $this->isProductApprovalRequiredAfterEdit()) ?  Status::STATUS_DISABLED:$productData['product']['status']);
                    }
                    $this->extractNonEditableData($model);
                    $model->save();
                }
                break;
        }
    }

    /**
     * Check Product Admin Approval required
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isProductApprovalRequired()
    {
        return $this->_scopeConfig->getValue(
            'ced_vproducts/general/confirmation',
            'store',
            $this->_storeManager->getStore()->getCode()
        );
    }


     /**
     * Check Product Admin Approval required After Edit
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isProductApprovalRequiredAfterEdit()
    {
        return $this->_scopeConfig->getValue(
            'ced_csmarketplace/vproducts/re_confirmation',
            'store',
            $this->_storeManager->getStore()->getCode()
        );
    }

    /**
     * Set Vproduct status
     *
     * @param $status
     * @return Vproducts
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStatus($status)
    {
        if ($this->getStoreId()) {
            $statusAttribute = $this->_productResourceFactory->create()->getAttribute('status');

            if ($statusAttribute->isScopeWebsite()) {
                $website = $this->_storeManager->getStore($this->getStoreId())->getWebsite();
                $stores = $website->getStoreIds();
            } else if ($statusAttribute->isScopeStore()) {
                $stores = array($this->getStoreId());
            } else {
                $stores = array_keys($this->_storeManager->getStores());
            }
        } else {
            $stores = array(0);//admin store
        }

        foreach ($stores as $store) {
            $statusModel = $this->_vProductStatusFactory->create()->loadByField(['product_id', 'store_id'],
                [$this->getProductId(), $store]);
            if ($statusModel && $statusModel->getId()) {
                if ($statusModel->getStatus() != $status) {
                    $statusModel->setStatus($status)->save();
                }
            } else {
                $statusModel = $this->_vProductStatusFactory->create();
                $statusModel->setStatus($status)
                    ->setStoreId($store)
                    ->setProductId($this->getProductId())
                    ->save();
            }
        }

        return $this;
    }

    /**
     * Remove Non Editable Attribute data from set values
     *
     * @param \Ced\CsMarketplace\Model\Vproducts $model
     */
    public function extractNonEditableData($model)
    {
        foreach (array('vendor_id', 'product_id') as $attribute_code) {
            $model->setData($attribute_code, $model->getOrigData($attribute_code));
        }
    }

    /**
     * Save Product Type Specific data
     *
     * @param $product
     * @param $productData
     * @return bool|Vproducts
     */
    private function saveTypeData($product, $productData)
    {
        $type = isset($productData['type']) ? $productData['type'] : Type::DEFAULT_TYPE;

        switch ($type) {
            case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE :
                $this->saveDownloadableData(
                    $product,
                    isset($productData['downloadable']) ? $productData['downloadable'] : []
                );
                break;

            default:
                return false;
        }

        return $this;
    }

    /**
     * Save Downloadable product data
     *
     * @param $product
     * @param $downloadableData
     * @return Vproducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveDownloadableData($product, $downloadableData)
    {
        /* Start uploading data */
        $samples = $this->_marketplaceLinkHelper->uploadDownloadableFiles(
            "samples",
            isset($downloadableData['sample']) ? $downloadableData['sample'] : []
        );

        $link_samples = $this->_marketplaceLinkHelper->uploadDownloadableFiles(
            "link_samples",
            isset($downloadableData['link']) ? $downloadableData['link'] : []
        );

        $links = $this->_marketplaceLinkHelper->uploadDownloadableFiles(
            "links",
            isset($downloadableData['link']) ? $downloadableData['link'] : []
        );

        /* Start saving links data */
        $this->_marketplaceLinkHelper->processLinksData(
            isset($downloadableData['link']) ? $downloadableData['link'] : [],
            $links,
            $link_samples,
            $product
        );

        $this->_marketplaceLinkHelper->processSamplesData(
            isset($downloadableData['sample']) ? $downloadableData['sample'] : [],
            $samples,
            $product
        );

        return $this;
    }

    /**
     * Get Vproduct status
     *
     * @param $storeId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStatus($storeId)
    {
        $statusModel = $this->_vProductStatusFactory->create()->loadByField(['product_id', 'store_id'],
            [$this->getProductId(), $storeId]);
        return ($statusModel && $statusModel->getId()) ? $statusModel->getStatus() : 0;
    }

    /**
     * Change Vproduct status
     * @param $productIds
     * @param $checkstatus
     * @return array|Vproducts
     * @throws \Exception
     */
    public function changeVproductStatus($productIds, $checkstatus)
    {
        foreach ($this->_storeManager->getStores() as $store) {
            $storeId[] = $store->getId();
        }
        $storeId = 0;

        if (is_array($productIds)) {
            $VproductCollection = $this->getCollection()->addFieldToFilter('product_id', ['in' => $productIds]);
            if (count($VproductCollection) > 0) {
                $ids = [];
                $errors = ['success' => 0, 'error' => 0];
                foreach ($VproductCollection as $row) {
                    if ($row && $row->getId()) {
                        if (!$this->_marketplaceDataHelper->canShow($row->getVendorId()) &&
                            $checkstatus != self::DELETED_STATUS
                        ) {
                            $errors['error'] = 1;
                            continue;
                        }

                        if ($row->getCheckStatus() != $checkstatus) {
                            $productId = $row->getProductId();

                            /* dispatch event when vendor's product status is changed */
                            $this->_manager->dispatch('csmarketplace_vendor_product_status_changed', [
                                'product' => $row,
                                'status' => $checkstatus
                            ]);

                            switch ($checkstatus) {
                                case self::APPROVED_STATUS:
                                    if ($row->getCheckStatus() == self::PENDING_STATUS) {
                                        // foreach ($storeId as $store_id) {
                                        $this->_productFactory->create()->load($productId)
                                            ->setStoreId($storeId)
                                            ->setStatus(Status::STATUS_ENABLED)
                                            ->save();
                                        //  }
                                        $row->setStatus(Status::STATUS_ENABLED);
                                    } else if ($row->getCheckStatus() == self::NOT_APPROVED_STATUS) {
                                        $statusCollection = $this->_vProductStatusFactory->create()
                                            ->getCollection()->addFieldtoFilter('product_id', $productId);

                                        foreach ($statusCollection as $statusrow) {
                                            // foreach ($storeId as $store_id) {
                                            $this->_productFactory->create()->load($productId)
                                                ->setStoreId($storeId)
                                                ->setStatus($statusrow->getStatus())
                                                ->save();
                                            //  }
                                        }
                                    }
                                    $errors['success'] = 1;
                                    break;

                                case self::NOT_APPROVED_STATUS:
                                    if ($row->getCheckStatus() == self::PENDING_STATUS) {
                                        $row->setStatus(Status::STATUS_ENABLED);
                                    } elseif ($row->getCheckStatus() == self::APPROVED_STATUS) {
                                        $statusCollection = $this->_vProductStatusFactory->create();
                                        $statusCollection->getCollection()
                                            ->addFieldtoFilter('product_id', $productId);

                                        foreach ($statusCollection as $statusrow) {
                                            // foreach ($storeId as $store_id) {
                                            $this->_productFactory->create()->load($productId)
                                                ->setStoreId($storeId)
                                                ->setStatus(Status::STATUS_DISABLED)
                                                ->save();
                                            // }
                                        }
                                    }
                                    $errors['success'] = 1;
                                    break;

                                case self::DELETED_STATUS:
                                    $errors['success'] = 1;
                                    break;
                            }

                            $ids[] = $productId;
                            $row->setCheckStatus($checkstatus);
                            $row->save();
                        } else {
                            $errors['success'] = 1;
                        }
                    }
                }
                if ($ids && !$this->_customerSession->getVendorId()) {
                    $this->_marketplaceMailHelper->sendProductNotificationEmail($ids, $checkstatus);
                }
                return $errors;
            }
            return $this;
        }
        return $this;
    }

    /**
     *Change Products Status (Hide/show products from frontend on vendor approve/disapprove)
     * @param $vendorIds
     * @param $status
     * @return bool|Vproducts
     */
    public function changeProductsStatus($vendorIds, $status)
    {
        if ($status == Vendor::VENDOR_NEW_STATUS) {
            return false;
        }

        if (is_array($vendorIds)) {
            foreach ($vendorIds as $vendorId) {
                $collection = $this->getVendorProducts('', $vendorId);
                foreach ($collection as $row) {
                    $productId = $row->getProductId();
                    if ($status == Vendor::VENDOR_DISAPPROVED_STATUS) {
                        $statusCollection = $this->_vProductStatusFactory->create()
                            ->getCollection()->addFieldtoFilter('product_id', $productId);

                        foreach ($statusCollection as $statusrow) {
                            $this->_productFactory->create()->load($productId)
                                ->setStoreId($statusrow->getStoreId())
                                ->setStatus(Status::STATUS_DISABLED);
                        }
                    } else if ($status == Vendor::VENDOR_APPROVED_STATUS) {
                        $statusCollection = $this->_vProductStatusFactory->create()
                            ->getCollection()->addFieldtoFilter('product_id', $productId);

                        foreach ($statusCollection as $statusrow) {
                            $this->_productFactory->create()->load($productId)
                                ->setStoreId($statusrow->getStoreId())
                                ->setStatus($statusrow->getStatus());
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get Product collection
     *
     * @param string $checkstatus
     * @param int $vendorId
     * @param int $productId
     * @return ResourceModel\Vproducts\Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getVendorProducts($checkstatus = '', $vendorId = 0, $productId = 0)
    {
        $vproducts = $this->getCollection();

        if ($checkstatus === '') {
            $vproducts->addFieldToFilter('check_status', ['neq' => self::DELETED_STATUS]);
        } else {
            $vproducts->addFieldToFilter('check_status', ['eq' => $checkstatus]);
        }
        if ($vendorId) {
            $vproducts->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        }
        if ($productId) {
            $vproducts->addFieldToFilter('product_id', ['eq' => $productId]);
        }

        return $vproducts;
    }

    /**
     * Delete Vendor Products
     * @param $vendorId
     */
    public function deleteVendorProducts($vendorId)
    {
        if ($vendorId) {
            $product_ids = $this->getVendorProductIds($vendorId);
            if (!empty($product_ids)) {
                $statusCollection = $this->_vProductStatusFactory->create()
                    ->getCollection()->addFieldtoFilter('product_id', ["in" => $product_ids]);

                foreach ($statusCollection as $statusrow) {
                    $statusrow->delete();
                }
                $this->_action->updateAttributes($product_ids, ['status' => Status::STATUS_DISABLED], 0);
            }
        }
    }

    /**
     * get Current vendor Product Ids
     *
     * @param int $vendorId
     * @return array $productIds
     */
    public function getVendorProductIds($vendorId = 0)
    {
        if (!empty($this->_vproducts)) {
            return $this->_vproducts;
        } else {
            $vendorId = $vendorId ? $vendorId : $this->_customerSession->getVendorId();
            $vCollection = $this->getVendorProducts('', $vendorId, 0);
            $product_ids = [];
            if (count($vCollection) > 0) {
                foreach ($vCollection as $data) {
                    array_push($product_ids, $data->getProductId());
                }
                $this->_vproducts = $product_ids;
            }
        }
        return $this->_vproducts;
    }

    /**
     * Authenticate vendor-products association
     *
     * @param  int $vendorId
     * @param int $productId
     * @return boolean
     */
    public function isAssociatedProduct($vendorId = 0, $productId = 0)
    {
        if (!$vendorId || !$productId) {
            return false;
        }

        $vProducts = $this->getVendorProductIds($vendorId);
        return (in_array($productId, $vProducts)) ? true : false;
    }

    /**
     * get Allowed WebsiteIds
     *
     * @return array websiteIds
     */
    public function getAllowedWebsiteIds()
    {
        return $this->_vendorModal->getWebsiteIds($this->_customerSession->getVendorId());
    }

    /**
     * @param $vid
     * @param $categoryId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCountcategory($vid, $categoryId)
    {
        $collection = $this->getVendorProducts(self::APPROVED_STATUS, $vid);
        $products = [];

        foreach ($collection as $productData) {
            array_push($products, $productData->getProductId());
        }

        $cedProductcollection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect($this->_configFactory->create()->getProductAttributes())
            ->addAttributeToFilter('entity_id', ['in' => $products])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', 4)
            ->addStoreFilter($this->_storeManager->getStore()->getId());

        $cat_id = $categoryId;
        if (isset($cat_id)) {
            $cedProductcollection->joinField(
                'category_id', 'catalog_category_product', 'category_id',
                'product_id = entity_id', null, 'left'
            )->addAttributeToSelect('*')
                ->addAttributeToFilter('category_id', [
                        ['finset', ['in' => explode(',', $cat_id)]]
                    ]
                );
        }

        return $cedProductcollection->count();
    }

    /**
     * Get products count in category
     * @param $categoryId
     * @return int
     */
    public function getProductCount($categoryId)
    {
        $vproducts = $this->getVendorProductIds();
        $productTable = $this->_resourceConnection->getTableName('catalog_category_product');
        $readConnection = $this->_resourceConnection->getConnection('read');
        $select = $readConnection->select();
        $select->from(
            ['main_table' => $productTable],
            [new \Zend_Db_Expr('COUNT(main_table.product_id)')]
        )->where('main_table.category_id = ?', $categoryId)
            ->where('main_table.product_id in (?)', $vproducts)
            ->group('main_table.category_id');

        $counts = $readConnection->fetchOne($select);
        return (int)$counts;
    }

    /**
     * @param int $productId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isApproved($productId = 0)
    {
        if ($productId) {
            $model = $this->loadByField(['product_id'], [$productId]);
            if ($model && $model->getId()) {
                if ($model->getCheckStatus() == self::APPROVED_STATUS) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }
}
