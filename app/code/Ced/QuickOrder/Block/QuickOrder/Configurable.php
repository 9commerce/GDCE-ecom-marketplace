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
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class Configurable extends \Magento\Framework\View\Element\Template
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
     * @var ProductRepository
     */
    public $productRepository;

    /**
     * 
     * @var \Magento\Catalog\Model\Product
     */
    public $productFactory;
    /**
     * @var StockItemRepository
     */
    public $stockItemRepository;

    /**
     * Configurable constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $productCollectionFactory
     * @param ProductRepository $productRepository
     * @param Product $productFactory
     * @param StockItemRepository $stockItemRepository
     * @param array $data
     */
	public function __construct(
		Context $context,
		Registry $registry,
		CollectionFactory $productCollectionFactory,
        ProductRepository  $productRepository,
		Product $productFactory,
        StockItemRepository $stockItemRepository,
		array $data = []
	) {
		$this->_coreRegistry = $registry;
		$this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
		$this->productFactory = $productFactory;
        $this->stockItemRepository = $stockItemRepository;
		parent::__construct($context, $data);
	}

    /**
     * @return mixed
     */
	public function getConfigurableProductValue(){
				$productId =  $this->_coreRegistry->registry('productId');
				$product = $this->productRepository->getById($productId);
				$data = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
				return $data;
	}

    /**
     * @return mixed
     */
	public function getTrId()
	{
		$trId =  $this->_coreRegistry->registry('trId');
		return $trId;
	}

    /**
     * @return mixed
     */
	public function getProductId()
	{
		$productId =  $this->_coreRegistry->registry('productId');
		return $productId;
	}

    /**
     * @return ProductRepository
     */
	public function getProductRepository(){
	    return $this->productRepository;
    }

    /**
     * @return Product
     */
    public function getProductFactory(){
	    return $this->productFactory;
    }

    /**
     * @return StockItemRepository
     */
    public function getstockItemRepository(){
	    return $this->stockItemRepository;
    }

}

