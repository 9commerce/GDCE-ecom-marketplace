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

namespace Ced\CsMarketplace\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Vproducts
 * @package Ced\CsMarketplace\Controller
 */
class Vproducts extends Vendor
{
    /**
     * Maximum Qty Allowed
     */
    const MAX_QTY_VALUE = 99999999.9999;
    
    /**
     * @var string
     */
    protected $mode = '';
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $type;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Vproducts constructor.
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
     */
    public function __construct(
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
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
    ) {
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->type = $type;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Initialize product saving
     *
     * @return \Magento\Catalog\Model\Product|string
     * @throws \Exception
     */
    protected function _initProductSave()
    {
        $product = $this->_initProduct();
        if ($product == \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE) {
            return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
        }
        $productData = $this->getRequest()->getPost('product');

        if ($productData) {
            $stock_data = isset($productData['stock_data']) ? $productData['stock_data'] : null;
            $this->_filterStockData($stock_data);
        }

        $product->addData($productData);
        /**
         * Initialize product categories
         */
        $categoryIds = $this->getRequest()->getPost('category_ids');
        if (null !== $categoryIds) {
            if (empty($categoryIds)) {
                $categoryIds = '';
            }
            $cats = explode(',', $categoryIds);
            $cats = array_unique($cats);
            $category_array = [];
            foreach ($cats as $value) {
                if (strlen($value)) {
                    $category_array [] = trim($value);
                }
            }
            $product->setCategoryIds($category_array);
        }

        if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
            $setId = (int)$this->getRequest()->getParam('set') ? (int)$this->getRequest()->getParam('set') :
                $this->productFactory->create()->getDefaultAttributeSetId();;
            $product->setAttributeSetId($setId);

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
            $product->setStatus($this->vproductsFactory->create()->isProductApprovalRequired() ?
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED :
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            if ($this->csmarketplaceHelper->isSharingEnabled()) {
                $websiteIds = isset($productData['website_ids']) ? $productData['website_ids'] : [];
            } else {
                $websiteIds = $websiteIds = [$this->storeManager->getStore()->getWebsiteId()];
            }
            $product->setWebsiteIds($websiteIds);
        }

        if ($this->storeManager->isSingleStoreMode()) {
            $product->setWebsiteIds([$this->storeManager->getStore(true)->getWebsite()->getId()]);
        }
        return $product;
    }

    /**
     * Initialize product from request parameters
     *
     * @return \Magento\Catalog\Model\Product|string
     * @throws \Exception
     */
    protected function _initProduct()
    {
        $productData = $this->getRequest()->getPost();
        $productId = $this->getRequest()->getParam('id');

        if ($productId) {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE;
        } else {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE;
        }

        $productData['entity_id'] = $productId;
        $product = $this->productFactory->create();
        $errors = [];
        try {
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE) {
                $product->setStoreId($this->getRequest()->getParam('store_switcher', 0));
                $vendorId = $this->_getSession()->getVendorId();
                if ($productId && $vendorId) {
                    $vendorProduct = $this->vproductsFactory->create()->isAssociatedProduct($vendorId, $productId);
                    if (!$vendorProduct) {
                        return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
                    }
                }
                $product->load($productId);
            } else if ($this->mode == \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE) {
                $product->setStoreId(0);
                $allowedType = $this->type->getAllowedType($this->storeManager->getStore(null)->getId());
                $type = $this->getRequest()->getParam('type');
                if (!(in_array($type, $allowedType))) {
                    return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
                }
            }
            $product->addData(isset($productData['product']) ? $productData['product'] : '');
            $product->validate();
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
        }
        $vproductModel = $this->vproductsFactory->create();
        $vproductModel->addData(isset($productData['product']) ? $productData['product'] : '');
        $vproductModel->addData(
            isset($productData['product']['stock_data']) ? $productData['product']['stock_data'] :''
        );
        $productErrors = $vproductModel->validate();

        if (is_array($productErrors)) {
            $errors = array_merge($errors, $productErrors);
        }

        if (!empty($errors)) {
            foreach ($errors as $message) {
                $this->messageManager->addErrorMessage($message);
            }
            return \Ced\CsMarketplace\Model\Vproducts::ERROR_IN_PRODUCT_SAVE;
        }
        return $product;

    }

    /**
     * Filter product stock data
     *
     * @param $stockData
     * @return $this|bool
     */
    protected function _filterStockData(&$stockData)
    {
        if ($stockData === null) {
            return false;
        }
        if (!isset($stockData['use_config_manage_stock'])) {
            $stockData['use_config_manage_stock'] = 1;
        }
        if (isset($stockData['qty']) && (float)$stockData['qty'] > self::MAX_QTY_VALUE) {
            $stockData['qty'] = self::MAX_QTY_VALUE;
        }
        if (isset($stockData['min_qty']) && (int)$stockData['min_qty'] < 0) {
            $stockData['min_qty'] = 0;
        }
        if (!isset($stockData['is_decimal_divided']) || $stockData['is_qty_decimal'] == 0) {
            $stockData['is_decimal_divided'] = 0;
        }

        return $this;
    }
}
