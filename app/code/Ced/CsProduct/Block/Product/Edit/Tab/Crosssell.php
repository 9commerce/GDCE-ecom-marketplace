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

namespace Ced\CsProduct\Block\Product\Edit\Tab;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;

class Crosssell extends Extended
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var LinkFactory
     */
    protected $_linkFactory;

    /**
     * @var CollectionFactory
     */
    protected $_setsFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var Vproducts
     */
    protected $_vproduct;

    /**
     * @var Visibility
     */
    protected $_visibility;

    /**
     * Crosssell constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param LinkFactory $linkFactory
     * @param CollectionFactory $setsFactory
     * @param ProductFactory $productFactory
     * @param Type $type
     * @param Status $status
     * @param Visibility $visibility
     * @param Registry $coreRegistry
     * @param Vproducts $vproduct
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        LinkFactory $linkFactory,
        CollectionFactory $setsFactory,
        ProductFactory $productFactory,
        Type $type,
        Status $status,
        Visibility $visibility,
        Registry $coreRegistry,
        Vproducts $vproduct,
        array $data = []
    ) {
        $this->_linkFactory = $linkFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->_coreRegistry = $coreRegistry;
        $this->_vproduct = $vproduct;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    /**
     * Retrieve currently edited product model
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Add filter
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('cross_sell_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);

        if ($this->getProduct() && $this->getProduct()->getId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }

        if ($this->isReadonly()) {
            $this->setFilterVisibility(false);
        }
    }

    protected function _prepareFilterButtons()
    {
        /* prepare reset button */

        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );

        /* prepare search button */

        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

    /**
     * Checks when this block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct() && $this->getProduct()->getCrosssellReadonly();
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $vproducts = [];
        $vproducts = $this->_vproduct->getVendorProductIds();

        /* @var $collection Collection */
        $collection = $this->_linkFactory->create()->useCrossSellLinks()
            ->getProductCollection()
            ->setProduct($this->getProduct())
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $vproducts]);

        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = [0];
            }
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {

            $this->addColumn(
                'in_products',
                [
                    'type' => 'checkbox',
                    'name' => 'in_products',
                    'values' => $this->_getSelectedProducts(),
                    'align' => 'center',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select',
                    'column_css_class' => 'col-select'
                ]
            );
        }

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->_type->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $sets = $this->_setsFactory
            ->create()
            ->setEntityTypeFilter(
                $this->_productFactory->create()->getResource()->getTypeId()
            )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->_visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable' => !$this->isReadonly(),
                'edit_only' => !$this->getProduct()->getId(),
                'header_css_class' => 'col-position',
                'column_css_class' => 'col-position',
                'filter_condition_callback' => [$this, 'filterProductPosition']
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig
                    ->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData(
            'grid_url'
        ) ? $this->_getData(
            'grid_url'
        ) : $this->getUrl(
            'csproduct/*/crosssellGrid',
            ['_current' => true]
        );
    }

    /**
     * Retrieve selected crosssell products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getProductsCrossSell();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedCrossSellProducts());
        }
        return $products;
    }

    /**
     * Retrieve crosssell products
     *
     * @return array
     */
    public function getSelectedCrossSellProducts()
    {
        $products = [];
        foreach ($this->_coreRegistry->registry('current_product')->getCrossSellProducts() as $product) {
            $products[$product->getId()] = ['position' => $product->getPosition()];
        }
        return $products;
    }

    /**
     * Apply `position` filter to cross-sell grid.
     *
     * @param Collection $collection $collection
     * @param Column\Extended $column
     * @return $this
     */
    public function filterProductPosition($collection, $column)
    {
        $collection->addLinkAttributeToFilter($column->getIndex(), $column->getFilter()->getCondition());
        return $this;
    }
}
