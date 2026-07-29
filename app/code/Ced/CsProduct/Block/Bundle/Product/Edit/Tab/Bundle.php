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

namespace Ced\CsProduct\Block\Bundle\Product\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle as BlockBundle;

class Bundle extends BlockBundle
{
    /**
     * @var null
     */
    protected $_product = null;

    /**
     * @var string
     */
    protected $_template = 'product/edit/bundle.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_cedcoreRegistry = null;

    /**
     * Bundle constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_cedcoreRegistry = $registry;
        parent::__construct($context, $registry, $data);
        $this->setData('area', 'adminhtml');
    }

    /**
     * Get Tab Url
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/bundle_product_edit/form', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        $this->addChild(
            'add_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Create New Option'),
                'class' => 'add',
                'id' => 'add_new_option',
                'on_click' => 'bOption.add()'
            ]
        );

        $this->setChild(
            'options_box',
            $this->getLayout()->createBlock(
                \Ced\CsProduct\Block\Bundle\Product\Edit\Tab\Bundle\Option::class,
                'adminhtml.catalog.product.edit.tab.bundle.option'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Check readonly Block
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getCompositeReadonly();
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }

    /**
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'product';
    }

    /**
     * @return mixed || object
     */
    public function getProduct()
    {
        return $this->_cedcoreRegistry->registry('product');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Bundle Items');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Bundle Items');
    }

    /**
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get parent tab code
     *
     * @return string
     */
    public function getParentTab()
    {
        return 'product-details';
    }
}
