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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vshops\Catalog\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;


/**
 * Class ListProduct
 * @package Ced\CsMarketplace\Block\Vshops\Catalog\Product
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     *
     */
    const SEARCH_QUERY_PARAM = 'ced_search';

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $_vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * ListProduct constructor.
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        array $data = []
    ) {
        $this->_vproductsFactory = $vproductsFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->catalogConfig = $catalogConfig;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Retrieve loaded category collection
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    protected function _getProductCollection()
    {
        $name_filter = $this->_coreRegistry->registry('name_filter');
        if ($this->_productCollection === null) {
            $cedLayer = $this->getLayer();
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            if ($this->_coreRegistry->registry('product')) {
                $cedCategories = $this->_coreRegistry->registry('product')
                    ->getCategoryCollection()->setPage(1, 1)
                    ->load();
                if ($cedCategories->count()) {
                    $this->setCategoryId(current($cedCategories->getIterator()));
                }
            }
            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $cedCategory = $this->categoryRepository->get($this->getCategoryId());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $cedCategory = null;
                }
                if ($cedCategory) {
                    $origCategory = $cedLayer->getCurrentCategory();
                    $cedLayer->setCurrentCategory($cedCategory);
                }
            }
            $vendorId = $this->_coreRegistry->registry('current_vendor')->getId();
            $collection = $this->_vproductsFactory->create()
                ->getVendorProducts(\Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS, $vendorId);
            $products = [];
            foreach ($collection as $productData) {
                array_push($products, $productData->getProductId());
            }
            $cedProductcollection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addAttributeToFilter('entity_id', ['in' => $products])
                ->addStoreFilter($this->getCurrentStoreId())
                ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->addAttributeToFilter('visibility', 4);

            /*if (isset($name_filter)) {
                $cedProductcollection->addAttributeToSelect('*')->setOrder('entity_id', $name_filter);
            }*/

            $cat_id = $this->getRequest()->getParam('cat-fil');
            if (isset($cat_id)) {
                $cedProductcollection->joinField(
                    'category_id', 'catalog_category_product', 'category_id',
                    'product_id = entity_id', null, 'left'
                )
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('category_id', array(
                        array('finset', array('in' => explode(',', $cat_id)))
                    ));
            }

            $nameQ = $this->getRequest()->getParam(self::SEARCH_QUERY_PARAM, false);
            if($nameQ){
              $cedProductcollection->addAttributeToFilter(
                  'name', ['like' => '%'.$nameQ.'%']
              );
            }
            $this->_productCollection = $cedProductcollection;
            $this->prepareSortableFieldsByCategory($cedLayer->getCurrentCategory());

            if ($origCategory) {
                $cedLayer->setCurrentCategory($origCategory);
            }
        }

        $this->_productCollection->getSize();
        $this->_productCollection->getSelect()->group('e.entity_id');
        return $this->_productCollection;
    }


    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
