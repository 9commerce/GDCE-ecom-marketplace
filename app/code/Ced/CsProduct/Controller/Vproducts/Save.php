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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\ScopeInterface;

class Save extends \Ced\CsMarketplace\Controller\Vproducts\Save
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $type;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set
     */
    protected $vproductsSet;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */
    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var string
     */
    protected $mode = '';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Save constructor.
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Initialization\Helper $initializationHelper
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $vproductsSet
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Ced\CsProduct\Controller\Vproducts\Initialization\Helper $initializationHelper,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $vproductsSet,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productBuilder = $productBuilder;
        $this->storeManager = $storeManager;
        $this->productCopier = $productCopier;
        $this->backendSession = $backendSession;
        $this->productTypeManager = $productTypeManager;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->initializationHelper = $initializationHelper;
        $this->vproductsFactory = $vproductsFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->type = $type;
        $this->vproductsSet = $vproductsSet;
        $this->productFactory = $productFactory;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->dataPersistor = $dataPersistor;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor,
            $storeManager,
            $productFactory,
            $vproductsFactory,
            $type,
            $logger,
            $categoryLinkManagement
        );
    }

    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _initProduct()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (!$this->scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            ScopeInterface::SCOPE_STORE,
            $storeId
        )) {
            return parent::_initProduct();
        }

        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE;
        } else {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE;
        }

        return $this->mode;
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $urlPath = 'csproduct/vproducts/';
        $urlParam = ['store' => $storeId];
        $currentStore = $this->storeManager->getStore();
        $currentStoreId = $currentStore->getId();
        $resultRedirect = $this->resultRedirectFactory->create();
        $store = $this->getStoreManager()->getStore($storeId);

        if (!$this->scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            ScopeInterface::SCOPE_STORE,
            $storeId
        )) {
            return parent::execute();
        }

        if ($this->scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            'store',
            $this->getStoreManager()->getStore()->getId()
        )) {
            $vendorId = $this->_getSession()->getVendorId();

            if (!$vendorId) {
                $url = $currentStore->getUrl($urlPath, $urlParam);
                return $resultRedirect->setPath($url);
            }

            $this->getStoreManager()->setCurrentStore($store->getCode());
            $redirectBack = $this->getRequest()->getParam('back', false);
            $productId = $this->getRequest()->getParam('id');
            $data = $this->getRequest()->getPostValue();
            $productAttributeSetId = $this->getRequest()->getParam('set');
            $productTypeId = $this->getRequest()->getParam('type');

            $vendorProduct = false;

            if ($productId && $vendorId) {
                $vendorProduct = $this->vproductsFactory->create();
                $vendorProduct->isAssociatedProduct($vendorId, $productId);
                if (!$vendorProduct) {
                    $url = $currentStore->getUrl($urlPath, $urlParam);
                    return $resultRedirect->setPath($url);
                }
            } else {
                if (count($this->vproductsFactory->create()->getVendorProductIds(
                    $this->_getSession()->getVendorId()
                    )) >= $this->csmarketplaceHelper->getVendorProductLimit()) {
                    $this->messageManager->addErrorMessage(__('Product Creation limit has Exceeded'));
                    $url = $currentStore->getUrl($urlPath, $urlParam);
                    return $resultRedirect->setPath($url);
                }
                if (!$this->validateSetAndType($currentStore)) {
                    $url = $currentStore->getUrl($urlPath . 'new', ['_current' => true]);
                    return $resultRedirect->setPath($url);
                }
            }

            if ($data) {
                $this->_initProduct();

                try {
                    $product = $this->initializationHelper->initialize(
                        $this->productBuilder->build($this->getRequest())
                    );

                    $this->productTypeManager->processProduct($product);
                    if (isset($data['product'][$product->getIdFieldName()])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product was unable to be saved. Please try again.')
                        );
                    }

                    $originalSku = $product->getSku();
                    $canSaveCustomOptions = $product->getCanSaveCustomOptions();
                    $product->save();

                    $this->handleImageRemoveError($data, $product->getId());
                    $this->getCategoryLinkManagement()->assignProductToCategories(
                        $product->getSku(),
                        $product->getCategoryIds()
                    );
                    $productId = $product->getEntityId();
                    $productAttributeSetId = $product->getAttributeSetId();
                    $productTypeId = $product->getTypeId();
                    $extendedData = $data;
                    $extendedData['can_save_custom_options'] = $canSaveCustomOptions;
                    $this->copyToStores($extendedData, $productId);

                    $product = $this->productRepository->getById($product->getId(), true, 0);

                    $this->registry->register('saved_product', $product);
                    $this->vproductsFactory->create()->setProductData($product)->saveProduct($this->mode);

                    $this->_eventManager->dispatch('csmarketplace_vendor_new_product_creation', [
                        'controller' => $this,
                        'product' => $product,
                        'vendor_id' => $this->_getSession()->getVendorId(),
                    ]);

                    $this->messageManager->addSuccessMessage(__('You saved the product.'));
                    $this->getDataPersistor()->clear('catalog_product');

                    $this->_eventManager->dispatch(
                        'controller_action_catalog_product_save_entity_after',
                        ['controller' => $this, 'product' => $product]
                    );

                    if ($redirectBack === 'duplicate') {
                        if (count($this->vproductsFactory->create()->getVendorProductIds(
                    $this->_getSession()->getVendorId()
                    )) >= $this->csmarketplaceHelper->getVendorProductLimit()) {
                    $this->messageManager->addErrorMessage(__('Product Creation limit has Exceeded'));
                    $url = $currentStore->getUrl($urlPath, $urlParam);
                    return $resultRedirect->setPath($url);
                }
                        $newProduct = $this->productCopier->copy($product);
                        $this->vproductsFactory->create()->processPostSave(
                            \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE,
                            $newProduct,
                            $productData = []
                        );
                        $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
                    }
                    $this->getDataPersistor()->clear('catalog_product');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $data = isset($product) ? $this->persistMediaData($product, $data) : $data;
                    $this->getDataPersistor()->set('catalog_product', $data);
                    $this->backendSession->setProductData($data);
                    $redirectBack = $productId ? true : 'new';
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $data = isset($product) ? $this->persistMediaData($product, $data) : $data;
                    $this->getDataPersistor()->set('catalog_product', $data);
                    $this->backendSession->setProductData($data);
                    $redirectBack = $productId ? true : 'new';
                }
            } else {
                $this->messageManager->addErrorMessage('No data to save');
                $url = $currentStore->getUrl($urlPath, $urlParam);
                return $resultRedirect->setPath($url);
            }

            if ($redirectBack === 'new') {
                $urlPath .= 'new';
                $urlParam = [
                    'set' => $productAttributeSetId, 'type' => $productTypeId
                ];
            } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
                $urlPath .= 'edit';
                $urlParam = [
                    'id' => $newProduct->getEntityId(), 'back' => null, '_current' => true
                ];
            } elseif ($redirectBack) {
                $urlPath .= 'edit';
                $urlParam = [
                    'id' => $productId, '_current' => true, 'set' => $productAttributeSetId
                ];
            }
        }

        $url = $currentStore->getUrl($urlPath, $urlParam);
        if(str_contains($url, "?")){
            $url = substr($url, 0, strpos($url, "?"));
        }
        return $resultRedirect->setPath($url);
    }

    /**
     * @param $currentStore
     * @param string $type
     * @param int $set
     * @return bool
     */
    public function validateSetAndType($currentStore, $type = '', $set = 0)
    {
        $allowedType = $this->type->getAllowedType($currentStore);
        $allowedSet = $this->vproductsSet->getAllowedSet($currentStore);

        $secretkey = time();

        if ($type == '') {
            $type = $this->getRequest()->getParam('type', $secretkey);
        }

        if ($set == 0) {
            $set = (int)$this->getRequest()->getParam('set', 0);
        }

        if ($type == $secretkey || (in_array($type, $allowedType) && in_array($set, $allowedSet))) {
            return true;
        }
        return false;
    }

    /**
     * Notify customer when image was not deleted in specific case.
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                $images = $product->getMediaGallery('images');
                if (is_array($images) && $expectedImagesAmount != count($images)) {
                    $this->messageManager->addNoticeMessage(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * Do copying data to stores
     *
     * If the 'copy_from' field is not specified in the input data,
     * the store fallback mechanism will automatically take the admin store's default value.
     *
     * @param array $data
     * @param int $productId
     * @return void
     */
    protected function copyToStores($data, $productId)
    {
        if (!empty($data['product']['copy_to_stores'])) {
            foreach ($data['product']['copy_to_stores'] as $websiteId => $group) {
                if (isset($data['product']['website_ids'][$websiteId])
                    && (bool)$data['product']['website_ids'][$websiteId]) {
                    foreach ($group as $store) {
                        if (isset($store['copy_from'])) {
                            $copyFrom = $store['copy_from'];
                            $copyTo = (isset($store['copy_to'])) ? $store['copy_to'] : 0;
                            $this->productFactory->create()
                                ->setStoreId($copyFrom)
                                ->load($productId)
                                ->setStoreId($copyTo)
                                ->setCanSaveCustomOptions($data['can_save_custom_options'])
                                ->setCopyFromView(true)
                                ->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Get categoryLinkManagement in a backward compatible way.
     *
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        return $this->categoryLinkManagement;
    }

    /**
     * Get storeManager in a backward compatible way.
     *
     * @return StoreManagerInterface
     * @deprecated 101.0.0
     */
    private function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Retrieve data persistor
     *
     * @return DataPersistorInterface|mixed
     * @deprecated 101.0.0
     */
    protected function getDataPersistor()
    {
        return $this->dataPersistor;
    }

    /**
     * Persist media gallery on error, in order to show already saved images on next run.
     *
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function persistMediaData(\Magento\Catalog\Api\Data\ProductInterface $product, array $data)
    {
        $mediaGallery = $product->getData('media_gallery');
        if (!empty($mediaGallery['images'])) {
            foreach ($mediaGallery['images'] as $key => $image) {
                if (!isset($image['new_file'])) {
                    //Remove duplicates.
                    unset($mediaGallery['images'][$key]);
                }
            }
            $data['product']['media_gallery'] = $mediaGallery;
            $fields = [
                'image',
                'small_image',
                'thumbnail',
                'swatch_image',
            ];
            foreach ($fields as $field) {
                $data['product'][$field] = $product->getData($field);
            }
        }

        return $data;
    }
}
