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

namespace Ced\CsProduct\Model;

use Ced\CsMarketplace\Helper\Mail;
use Ced\CsMarketplace\Helper\Vproducts\Image;
use Ced\CsMarketplace\Helper\Vproducts\Link;
use Magento\Catalog\Model\ConfigFactory;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

class Vproducts extends \Ced\CsMarketplace\Model\Vproducts
{

    /**
     * @var array
     */
    protected $_vproducts = [];

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendorModal;

    /**
     * Vproducts constructor.
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param \Ced\CsMarketplace\Model\Vproducts\StatusFactory $vProductStatusFactory
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
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Ced\CsMarketplace\Model\Vproducts\StatusFactory $vProductStatusFactory,
        \Ced\CsMarketplace\Model\Vendor $vendorModal,
        Link $marketplaceLinkHelper,
        Image $marketplaceImageHelper,
        Mail $marketplaceMailHelper,
        \Ced\CsMarketplace\Model\Session $_customerSession,
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
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->customerSession = $_customerSession;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorModal = $vendorModal;
        parent::__construct(
            $marketplaceDataHelper,
            $vProductStatusFactory,
            $vendorModal,
            $marketplaceLinkHelper,
            $marketplaceImageHelper,
            $marketplaceMailHelper,
            $_customerSession,
            $action,
            $configFactory,
            $productResourceFactory,
            $dataObjectHelper,
            $stockItemInterfaceFactory,
            $moduleManager,
            $manager,
            $request,
            $scopeConfig,
            $resourceConnection,
            $productFactory,
            $storeManager,
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
     * Check Product Admin Approval required
     */
    public function isProductApprovalRequired()
    {
        return $this->scopeConfig->getValue(
            'ced_vproducts/general/confirmation',
            'store',
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param $mode
     * @return $this|\Ced\CsMarketplace\Model\Vproducts|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveProduct($mode)
    {
        $scopeConfig = $this->scopeConfig;
        if (!$scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            ScopeInterface::SCOPE_STORE
        )) {
            return parent::saveProduct($mode);
        }

        $register = $this->registry;
        if ($scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            'store',
            $this->storeManager->getStore()->getId()
        )) {
            $product = [];
            if ($register->registry('saved_product') != null) {
                $product = $register->registry('saved_product');
            }

            $productData = [];
            /**
             * Relate Product data
             * @params int mode,int $productId,array $productData
             */
            $vproductModel = $this->processPostSave($mode, $product, $productData);

        }
        return $this;
    }

    /**
     * Relate Product Data
     * @params $mode,int $productId,array $productData
     */

    public function processPostSave($mode, $product, $productData)
    {
        if (!$this->scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            ScopeInterface::SCOPE_STORE
        )) {
            return parent::processPostSave($mode, $product, $productData);
        }

        $stockRegistry = $this->stockRegistry;
        $stockitem = $stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );
        $qty = $stockitem->getQty();
        $is_in_stock = $stockitem->getIsInStock();

        $productId = $product->getId();
        $websiteIds = '';
        if (isset($productData['product']['website_ids'])) {
            $websiteIds = implode(",", $productData['product']['website_ids']);
        } elseif ($this->_registry->registry('ced_csmarketplace_current_website') != '') {
            $websiteIds = $this->_registry->registry('ced_csmarketplace_current_website');
        } else {
            $websiteIds = implode(",", $product->getWebsiteIds());
        }
        $vendorId = $this->customerSession->getVendorId();

        switch ($mode) {
            case self::NEW_PRODUCT_MODE:
                $vproductsModel = $this->vproductsFactory->create();
                $vproductsModel->setVendorId($vendorId);
                $vproductsModel->setProductId($productId);
                $vproductsModel->setData('type', $product->getTypeId());
                $vproductsModel->setPrice($product->getPrice());
                $vproductsModel->setSpecialPrice($product->getSpecialPrice());
                $vproductsModel->setName($product->getName());
                $vproductsModel->setDescription($product->getDescription());
                $vproductsModel->setSku($product->getSku());
                if (isset($productData['default_approval'])) {
                    $vproductsModel->setCheckStatus(self::APPROVED_STATUS);
                } else {
                    $vproductsModel->setCheckStatus(
                        $this->isProductApprovalRequired() ? self::PENDING_STATUS : self::APPROVED_STATUS
                    );
                }
                $vproductsModel->setQty($qty);
                $vproductsModel->setIsInStock($is_in_stock);
                $vproductsModel->setWebsiteId($websiteIds);
                return $vproductsModel->save();

            case self::EDIT_PRODUCT_MODE:
                $model = $this->loadByField(['product_id'], [$product->getId()]);
                if ($model && $model->getId()) {
                    $model->setData('type', $product->getTypeId());
                    $model->setPrice($product->getPrice());
                    $model->setSpecialPrice($product->getSpecialPrice());
                    $model->setName($product->getName());
                    $model->setDescription($product->getDescription());
                    $model->setShortDescription($product->getShortDescription());
                    $model->setSku($product->getSku());
                    $model->setQty($qty);
                    $model->setIsInStock($is_in_stock);
                    $model->setWebsiteId($websiteIds);
                    $model->setCheckStatus(($this->isProductApprovalRequired() && $this->isProductApprovalRequiredAfterEdit()) ? self::PENDING_STATUS : $model->getCheckStatus());
                    return $model->save();
                }
        }
        return $this;
    }

    /**
     * get Allowed WebsiteIds
     *
     * @return array websiteIds
     */
    public function getAllowedWebsiteIds()
    {
        $webisteIds = $this->vendorModal
            ->getWebsiteIds($this->customerSession->getVendorId());
        return $webisteIds;
    }
}
