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

namespace Ced\CsProduct\Block;

use Magento\Catalog\Model\Product\TypeFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type;
use Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Container;
use Magento\Framework\Exception\LocalizedException;

class Product extends Container
{
    /**
     * @var string
     */
    protected $_template = 'product.phtml';

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var Set
     */
    protected $set;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Product constructor.
     * @param TypeFactory $typeFactory
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param Type $type
     * @param Set $set
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TypeFactory $typeFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        Type $type,
        Set $set,
        Context $context,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
        $this->storeManager = $storeManager;
        $this->type = $type;
        $this->set = $set;
        parent::__construct($context, $data);
    }

    /**
     * @return Container
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \Ced\CsProduct\Block\Widget\Button\SplitButton::class,
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );
        $allowedType = $this->type->getAllowedType($this->storeManager->getStore()->getId());
        foreach ($types as $typeId => $type) {
            if (!in_array($typeId, $allowedType)) {
                continue;
            }
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => sprintf(
                    "location.href = '%s';",
                    $this->_getProductCreateUrl($typeId)
                ),
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
                'href' => $this->_getProductCreateUrl($typeId)
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        $attributeSetId = $this->_productFactory->create()->getDefaultAttributeSetId();

        $allowedSet = $this->set->getAllowedSet($this->storeManager->getStore()->getId());
        if (is_array($allowedSet)) {
            $attributeSetId = current($allowedSet);
        }
        return $this->getUrl(
            'csproduct/*/new',
            ['set' => $attributeSetId, 'type' => $type]
        );
    }

    /**
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            'area' => 'adminhtml'
        ];

        return $splitButtonOptions;
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            '*/*/new'
        );
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
