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
namespace Ced\CsProduct\Controller\Product\Builder;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Ced\CsProduct\Controller\Product\Builder as CatalogProductBuilder;
use Magento\Framework\App\RequestInterface;

class Plugin
{
    /**
     * @var ProductFactory
     */
    protected $cedproductFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $cedconfigurableType;

    /**
     * @param ProductFactory $cedproductFactory
     * @param Type\Configurable $cedconfigurableType
     */
    public function __construct(ProductFactory $cedproductFactory, Type\Configurable $cedconfigurableType)
    {
        $this->cedproductFactory = $cedproductFactory;
        $this->cedconfigurableType = $cedconfigurableType;
    }

    /**
     * Set type and data to configurable product
     *
     * @param CatalogProductBuilder $subject
     * @param Product $product
     * @param RequestInterface $request
     * @return Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterBuild(CatalogProductBuilder $subject, Product $product, RequestInterface $request)
    {
        if ($request->has('attributes')) {
            $attributes = $request->getParam('attributes');
            if (!empty($attributes)) {
                $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
                $this->cedconfigurableType->setUsedProductAttributes($product, $attributes);
            } else {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            }
        }

        // Required attributes of simple product for configurable creation
        if ($request->getParam('popup') && ($requiredAttributes = $request->getParam('required'))) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($request->getParam('popup')
            && $request->getParam('product')
            && !is_array($request->getParam('product'))
            && $request->getParam('id', false) === false
        ) {
            $configProduct = $this->cedproductFactory->create();
            $configProduct->setStoreId(0)
                ->load($request->getParam('product'))
                ->setTypeId($request->getParam('type'));

            $data = [];
            foreach ($configProduct->getTypeInstance()->getSetAttributes($configProduct) as $attribute) {
                /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                if (!$attribute->getIsUnique() &&
                    $attribute->getFrontend()->getInputType() != 'gallery' &&
                    $attribute->getAttributeCode() != 'required_options' &&
                    $attribute->getAttributeCode() != 'has_options' &&
                    $attribute->getAttributeCode() != $configProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }
            $product->addData($data);
            $product->setWebsiteIds($configProduct->getWebsiteIds());
        }

        return $product;
    }
}
