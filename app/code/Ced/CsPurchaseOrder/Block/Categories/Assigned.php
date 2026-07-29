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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Block\Categories;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;

class Assigned extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Framework\Module\Manager $moduleManager,
        Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Registry $registry,
        UrlFactory $urlFactory,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Store\Model\ResourceModel\Group\Collection $groupCollection,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Ced\CsMarketplace\Block\Vproducts\Store\Switcher $_storeSwitcher,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $_configVproductType,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    )
    {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->_registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->_categoryModel = $categoryModel;
        $this->_groupCollection = $groupCollection;
        $this->_vproducts = $vproducts;
        $this->_priceCurrency = $priceCurrency;
        $this->_configVproductType = $_configVproductType;
        $this->_storeManager = $context->getStoreManager();
        $this->_vproductsFactory = $vproductsFactory->create();
        $this->_productCollectionFactory = $productCollection->create();
        $this->_type = $type;
        $this->_storeSwitcher = $_storeSwitcher;
    }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get Category IDs
     *
     */
    public function getCategoryIds()
    {
        $_product = $this->getProduct();
        $category_ids = [];
        if ($_product) {
            $category_ids = $_product->getCategoryIds();
        }
        if (is_array($category_ids) && empty($category_ids)) {
            $category_ids = [];
        }
        return $category_ids;
    }

    public function getCategoryModel(){
        return $this->_categoryModel;
    }

    public function getGroups(){
        return $this->_groupCollection
            ->addFieldToFilter('group_id', array('neq'=>0))->setOrder('website_id', 'ASC');
    }
}
