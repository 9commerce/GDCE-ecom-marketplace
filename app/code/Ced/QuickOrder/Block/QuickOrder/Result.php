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
 * @package     Ced_QuickOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\QuickOrder\Block\QuickOrder;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Result extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
	public $_coreRegistry;

    /**
    
    * @var CollectionFactory
     */
	public $_productCollectionFactory;

    /**
     * @var CurrencyFactory
     */
	public $currencyStoreFactory;

    /**
     * @var StockRegistryInterface
     */
	public $stockRegistry;

    /**
     * @var ImageBuilder
     */
	public $imageBuilder;

    /**
     * @var Stock
     */
    public $_stockFilter;

    /**
     * @var PriceCurrencyInterface
     */
    public $priceCurrencyInterface;

    /**
     * @var StoreManagerInterface
     */
    public $storeManagerInterface;

    /**
     * Result constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $productCollectionFactory
     * @param CurrencyFactory $currencyStoreFactory
     * @param StockRegistryInterface $stockRegistry
     * @param ImageBuilder $imageBuilder
     * @param Stock $stockFilter
     * @param PriceCurrencyInterface $priceCurrencyInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $data
     */

	public function __construct(
		Context $context,
		Registry $registry,
		CollectionFactory $productCollectionFactory,
		CurrencyFactory $currencyStoreFactory,
        StockRegistryInterface $stockRegistry,
        ImageBuilder $imageBuilder,
        Stock $stockFilter,
        PriceCurrencyInterface $priceCurrencyInterface,
        StoreManagerInterface $storeManagerInterface,
		array $data = []
	) {
		$this->_coreRegistry = $registry;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->currencyStoreFactory = $currencyStoreFactory;
		$this->stockRegistry = $stockRegistry;
        $this->imageBuilder = $imageBuilder;
        $this->_stockFilter = $stockFilter;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->storeManagerInterface = $storeManagerInterface;
		parent::__construct($context, $data);

	}

    /**
     * @return collection
     */
	public function getResult(){
		$productCollection = $this->_productCollectionFactory;
		$collection = $productCollection->create()
		->addAttributeToSelect('*');
			 $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
             $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
 			 $collection->addAttributeToFilter([
                ['attribute' => 'name','like' => '%'.$this->_coreRegistry->registry('query').'%'],
                ['attribute' => 'sku','like' => '%'.$this->_coreRegistry->registry('query').'%'],
             ])
             ->addStoreFilter($this->getStoreId())
 			 ->addAttributeToFilter('status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
             ->addAttributeToFilter('visibility',4)
 			 ->addAttributeToFilter([
				['attribute'=>'type_id','eq'=>'simple'],
	 			['attribute'=>'type_id','eq'=>'configurable'],
 				['attribute'=>'type_id','eq'=>'virtual'],
 			 ]);
        $this->_stockFilter->addInStockFilterToCollection($collection);
		return $collection;
	}

    /**
     * @return mixed
     */
	public function getTrId(){
		$hiddenTrId = $this->_coreRegistry->registry('hiddenTrId');
		return $hiddenTrId;
	}

	/**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        $currentCurrencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyStoreFactory->create()->load($currentCurrencyCode);
		$currencySymbol = $currency->getCurrencySymbol();
		if($currencySymbol){
		     return $currencySymbol;
		} else{
		    return $currentCurrencyCode;
		}
    }

    /**
     * @return mediaUrl
     */
    public function getMediaUrl(){
        $currentStore = $this->_storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * @return directoryPath
     */
    public function getDirectoryPath(){
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $directoryPath = $mediaDirectory->getAbsolutePath();
        return $directoryPath;
    }

    /**
     * @return stockRegistryObject
     */
    public function getstockRegistry(){
        return $this->stockRegistry;
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return mixed
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    public function getPriceCurrencyInterface(){
        return $this->priceCurrencyInterface;
    }

    public function getStoreManagerInterface(){
        return $this->storeManagerInterface;
    }
}


