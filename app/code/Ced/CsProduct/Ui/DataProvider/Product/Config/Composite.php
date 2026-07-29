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

namespace Ced\CsProduct\Ui\DataProvider\Product\Config;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Ced\CsProduct\Ui\DataProvider\Product\Config\Data\AssociatedProducts;
use Magento\Catalog\Ui\AllowedProductTypes;

class Composite extends AbstractModifier
{
    /**
     * @var array
     */
    private $modifiers = [];

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var AssociatedProducts
     */
    private $associatedProducts;

    /**
     * @var AllowedProductTypes
     */
    protected $allowedProductTypes;

    /**
     * @var ModifierInterface[]
     */
    private $modifiersObjects = [];

    /**
     * Composite constructor.
     * @param LocatorInterface $locator
     * @param AssociatedProducts $associatedProducts
     * @param AllowedProductTypes $allowedProductTypes
     * @param array $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        AssociatedProducts $associatedProducts,
        AllowedProductTypes $allowedProductTypes,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->associatedProducts = $associatedProducts;
        $this->allowedProductTypes = $allowedProductTypes;
        $this->modifiers = $modifiers;

        foreach ($this->modifiers as $modifierClass) {
            /** @var ModifierInterface $bundleModifier */
            $modifier = \Magento\Framework\App\ObjectManager::getInstance()->get($modifierClass);
            if (!$modifier instanceof ModifierInterface) {
                throw new \InvalidArgumentException(__(
                    'Type %1 is not an instance of %2',
                    $modifierClass,
                    ModifierInterface::class
                ));
            }
            $this->modifiersObjects[] = $modifier;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $model */
        $model = $this->locator->getProduct();
        $productTypeId = $model->getTypeId();
        if ($this->allowedProductTypes->isAllowedProductType($this->locator->getProduct())) {
            $productId = $model->getId();
            $data[$productId]['affect_configurable_product_attributes'] = '1';

            if ($productTypeId === ConfigurableType::TYPE_CODE) {
                $data[$productId]['configurable-matrix'] = $this->associatedProducts->getProductMatrix();
                $data[$productId]['attributes'] = $this->associatedProducts->getProductAttributesIds();
                $data[$productId]['attribute_codes'] = $this->associatedProducts->getProductAttributesCodes();
                $data[$productId]['product']['configurable_attributes_data'] =
                    $this->associatedProducts->getConfigurableAttributesData();
            }
        }

        foreach ($this->modifiersObjects as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->allowedProductTypes->isAllowedProductType($this->locator->getProduct())) {
            foreach ($this->modifiersObjects as $modifier) {
                $meta = $modifier->modifyMeta($meta);
            }
        }

        return $meta;
    }
}
