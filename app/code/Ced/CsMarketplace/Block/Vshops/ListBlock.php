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

namespace Ced\CsMarketplace\Block\Vshops;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data as CsMarketplaceHelperData;
use Ced\CsMarketplace\Helper\Tool\Image;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\Vshop;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Url\Helper\Data as UrlHelperData;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelperData;

/**
 * Class ListBlock
 * @package Ced\CsMarketplace\Block\Vshops
 */
class ListBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * @var int
     */
    protected $_defaultColumnCount = 5;

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_cedCatalogLayer;

    /**
     * @var Vshop
     */
    protected $vshop;

    /**
     * @var UrlHelperData
     */
    protected $urlHelper;

    /**
     * @var Vendor Collection
     */
    protected $_vendorCollection;

    protected $_request;

    /**
     * @var Vendor
     */
    protected $vendor;

    /**
     * @var CsMarketplaceHelperData
     */
    protected $csmarketplaceHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductList
     */
    public $_prodListHelper;

    /**
     * @var Image
     */
    protected  $imageHelper;

    /**
     * @var Acl
     */
    protected  $aclHelper;

    /**
     * @var TaxHelperData
     */
    protected  $magentoTaxHelper;

    /**
     * @var Data
     */
    protected $magentoDirectoryHelper;

    /**
     * ListBlock constructor.
     * @param Context $context
     * @param Image $imageHelper
     * @param Acl $aclHelper
     * @param TaxHelperData $magentoTaxHelper
     * @param Data $magentoDirectoryHelper
     * @param Resolver $layerResolver
     * @param UrlHelperData $urlHelper
     * @param Vshop $vshop
     * @param Vendor $vendor
     * @param CsMarketplaceHelperData $csmarketplaceHelper
     * @param StoreManagerInterface $storeManager
     * @param ProductList $prodListHelper
     * @param array $data
     */
    public function __construct(
        Image $imageHelper,
        Acl $aclHelper,
        TaxHelperData $magentoTaxHelper,
        Data $magentoDirectoryHelper,
        Resolver $layerResolver,
        UrlHelperData $urlHelper,
        Vshop $vshop,
        Vendor $vendor,
        CsMarketplaceHelperData $csmarketplaceHelper,
        ProductList $prodListHelper,
        Context $context,
        array $data = []
    ) {
        $this->magentoDirectoryHelper = $magentoDirectoryHelper;
        $this->magentoTaxHelper = $magentoTaxHelper;
        $this->aclHelper = $aclHelper;
        $this->imageHelper = $imageHelper;
        $this->_cedCatalogLayer = $layerResolver->get();
        $this->urlHelper = $urlHelper;
        $this->_request = $context->getRequest();
        $this->vshop = $vshop;
        $this->vendor = $vendor;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_prodListHelper = $prodListHelper;
        parent::__construct($context, $data);
    }
    
     /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setKeywords($this->_scopeConfig->getValue(
            'ced_vseo/general/meta_keywords',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));

        $this->pageConfig->setDescription($this->_scopeConfig->getValue(
            'ced_vseo/general/meta_description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve loaded category collection
     *
     */
    protected function _getVendorCollection()
    {
        $vendor_name = $this->_request->getParam('char');
        $name_filter = $this->_request->getParam('product_list_dir');
        $zip_code = $this->_request->getParam('estimate_postcode');
        $country = $this->_request->getParam('country_id');
        $city = $this->_request->getParam('estimate_city');

        if (!$this->_vendorCollection) {
            $vendorIds = [0];
            $model = $this->vshop->getCollection()
                ->addFieldToFilter('shop_disable', Vshop::DISABLED);

            if (count($model) > 0) {
                foreach($model as $row){
                    $vendorIds[] = $row->getVendorId();
                }
            }

            $websiteId = $this->_storeManager->getStore()->getWebsiteId();

            $this->_vendorCollection = $this->vendor->getCollection();
            $this->_vendorCollection->addFieldToFilter("website_id", $websiteId)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', Vendor::VENDOR_APPROVED_STATUS);

            if ($name_filter == '') {
                $this->_vendorCollection->addAttributeToSort('public_name', 'asc');
            }

            if (count($vendorIds) > 0) {
                if ($vendor_name != "") {
                    $this->_vendorCollection->addAttributeToFilter([
                        ['attribute' => 'public_name', 'like' => '%' . $vendor_name . '%'],
                    ]);
                }

                if ($country != "") {
                    $this->_vendorCollection->addAttributeToFilter([
                        ['attribute' => 'country_id', 'eq' => $country]
                    ]);
                }

                if (($region = $this->_request->getParam('region')) != '') {
                    $this->_vendorCollection->addAttributeToFilter(
                        [['attribute' => 'region', 'eq' => $region]]
                    );
                }

                if (empty($region) && ($region_id = $this->_request->getParam('region_id')) != '') {
                    $this->_vendorCollection->addAttributeToFilter(
                        [['attribute' => 'region_id', 'eq' => $region_id]]
                    );
                }

                if ($city != '') {
                    $this->_vendorCollection->addAttributeToFilter(
                        [['attribute'=>'city','like' => '%'.$city.'%']]
                    );
                }

                if ($zip_code != '') {
                    $this->_vendorCollection->addAttributeToFilter(
                        [['attribute'=>'zip_code','like' => '%'.$zip_code.'%']]
                    );
                }

                if ($name_filter != '') {
                    $this->_vendorCollection->addAttributeToSort('public_name', $name_filter);
                }

                $this->_vendorCollection->addAttributeToFilter('entity_id', ['nin' => $vendorIds]);
            }

            if (!$this->csmarketplaceHelper->isSharingEnabled()) {
                $this->_vendorCollection->addAttributeToFilter(
                    'website_id',
                    $this->_storeManager->getStore()->getWebsiteId()
                );
            }
            $this->prepareSortableFields();
        }

        return $this->_vendorCollection;
    }

    /**
     * Retrieve loaded category collection
     *
     */
    public function getLoadedVendorCollection()
    {
        return $this->_getVendorCollection();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Prepare Sort By fields from Category Data for Vshops
     * @return $this
     */
    public function prepareSortableFields()
    {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($this->_getConfig()->getAttributeUsedForSortByArray());
        }
        $cedAvailableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($defaultSortBy = $this->_getConfig()->getDefaultSortBy()) {
                if (isset($cedAvailableOrders[$defaultSortBy])) {
                    $this->setSortBy($defaultSortBy);
                }
            }
        }
        return $this;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_cedCatalogLayer;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        $currentMode = $this->getChildBlock('toolbar')->getCurrentMode();
        return $currentMode;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getVendorCollection();

        // use sortable parameters
        $cedorders = $this->getAvailableOrders();
        if ($cedorders) {
            $toolbar->setAvailableOrders($cedorders);
        }
        $cedsort = $this->getSortBy();
        if ($cedsort) {
            $toolbar->setDefaultOrder($cedsort);
        }
        $ceddir = $this->getDefaultDirection();
        if ($ceddir) {
            $toolbar->setDefaultDirection($ceddir);
        }
        $cedmodes = $this->getModes();
        if ($cedmodes) {
            $toolbar->setModes($cedmodes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        /*$this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getVendorCollection()]
        );*/

        $this->_getVendorCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToolbarBlock()
    {
        $cedBlockName = $this->getToolbarBlockName();
        if ($cedBlockName) {

            $cedBlock = $this->getLayout()->getBlock($cedBlockName);
            if ($cedBlock) {
                return $cedBlockName;
            }
        }

        $cedBlockName = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $cedBlockName;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomPagerHtml()
    {
        $pagerBlock = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager');
        if ($pagerBlock instanceof DataObject) {
            /* @var \Magento\Theme\Block\Html\Pager $pagerBlock */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());
            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setLimitVarName('product_list_limit')
                ->setPageVarName('p')
                ->setModeVarName('product_list_mode')
                ->setLimit($this->getLimit());
            $pagerBlock->setCollection($this->_getVendorCollection());
            $this->_getVendorCollection()->load();
            return $pagerBlock->toHtml();
        }
        return '';
    }

    /**
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->_prodListHelper->getAvailableLimit($this->getMode());
    }

    /**
     * @return mixed|string
     */
    public function getLimit()
    {
        return $this->getChildBlock('toolbar')->getLimit();
        /*$limits = $this->getAvailableLimit();
        if ($limit = $this->getRequest()->getParam('product_list_limit')) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }
        $defaultLimit = $this->getDefaultPerPageValue();
        if ($defaultLimit && isset($limits[$defaultLimit])) {
            return $defaultLimit;
        }
        $limits = array_keys($limits);
        return $limits[0];*/
    }

    /**
     * @return string
     */
    public function getDefaultPerPageValue()
    {
        return $this->_prodListHelper->getDefaultLimitPerPageValue($this->getCurrentMode());
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        $cedAdditionalHtml = $this->getChildHtml('additional');
        return $cedAdditionalHtml;
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        $cedToolbar = $this->getChildHtml('toolbar');
        return $cedToolbar;
    }

    /**
     * @param Set AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_vendorCollection = $collection;
        return $this;
    }

    /**
     * @param array|string|integer|\Magento\Framework\App\Config\Element $code
     * @return $this
     */
    public function addAttribute($code)
    {
        $this->_getVendorCollection()->addAttributeToSelect($code);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceBlockTemplate()
    {
        $cedPriceBlock = $this->_getData('price_block_template');
        return $cedPriceBlock;
    }

    /**
     * Retrieve Catalog Config object
     *
     * @return Vendor
     */
    protected function _getConfig()
    {
        return $this->vendor;
    }


    /**
     * Get post parameters
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $cedUrl = $this->getAddToCartUrl($product);
        return [
            'action' => $cedUrl,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($cedUrl),
            ]
        ];
    }

    /**
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();
        $productPrice = '';
        if ($priceRender) {
            $productPrice = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $productPrice;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_defaultColumnCount;
    }

    /**
     * @return Acl
     */
    public function aclHelper()
    {
        return $this->aclHelper;
    }

    /**
     * @return Image
     */
    public function imageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * @return TaxHelperData
     */
    public function getMagentoTaxHelper()
    {
        return $this->magentoTaxHelper;
    }

    /**
     * @return Data
     */
    public function getMagentoDirectoryHelper()
    {
        return $this->magentoDirectoryHelper;
    }

}
