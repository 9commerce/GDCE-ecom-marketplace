<?php

namespace Ced\CsGst\Block;

use Magento\Catalog\Model\Product\Interceptor as ProductInterceptor;
use Magento\Checkout\Block\Cart\Additional\Info as AdditionalBlockInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ProductFactory;

class HsnCodeOnCart extends ViewTemplate
{
 
    /**
     * Product Factory
     *
     * @var productFactory
     */
    protected $productFactory;
    
    public $product;
    public $entityId;

    /**
     * HsnCodeOnCart constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
    }

    /**
     * Get vendor Name
     *
     * @return string
     */
    public function getHsnCode()
    {
        $product = $this->getProduct(); 
        $entityId = $this->getProductId(); 
        if ($entityId) {  
            $product = $product->load($entityId);
            if ($product) { 
                $code = $product->getHsn(); 
                if (!$code) {
                    $code = '';
                }
            }else{
                $code = '';
            }
        }else{
            $code = '';
        }      

        return $code;
    }

    /**
     * Get entity id of product from quote item
     *
     * @return int | bool
     */
    public function getProductId()
    { 

        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $e) { 
            return false;
        }

        /** @var AdditionalBlockInfo $block */
        $block = $layout->getBlock('additional.product.info');

        $entityId = false;
        
        if ($block instanceof AdditionalBlockInfo) {
            $item = $block->getItem(); 
            $entityId = $item->getProduct()->getEntityId();
        }

        return $entityId ;
    }
    /**
     * Get Product
     *
     * @return Object ProductInterceptor
     */
    public function getProduct()
    { 
        $product = $this->productFactory->create();
        $this->product = $product;
        return $product;
    }
}