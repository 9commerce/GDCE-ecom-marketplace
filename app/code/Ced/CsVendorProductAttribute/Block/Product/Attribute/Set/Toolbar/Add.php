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
  * @package   Ced_CsVendorProductAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsVendorProductAttribute\Block\Product\Attribute\Set\Toolbar;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class Add
 * @package Ced\CsVendorProductAttribute\Block\Product\Attribute\Set\Toolbar
 */
class Add extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Ced_CsVendorProductAttribute::catalog/product/attribute/set/toolbar/add.phtml';

    public function _construct()
    {
    	parent::_construct();
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Save'),
                    'class' => 'save primary save-attribute-set',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#set-prop-form']],
                    ],
            		'area' => 'adminhtml'
                ]
            );
            $this->getToolbar()->addChild(
                'back_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Back'),
            		'on_click' => sprintf("location.href = '%s';",
                        $this->getUrl('csvendorproductattribute/*/')),
                    'class' => 'back',
            		'area' => 'adminhtml'
                ]
            );
        }

        $objId = $this->getRequest()->getParam('id');

        if (!empty($objId)) {
        	$this->getToolbar()->addChild(
        			'delete_button',
        			'Magento\Backend\Block\Widget\Button',
        			[
        			'label' => __('Delete'),
        			'on_click' => sprintf("location.href = '%s';", $this->getDeleteUrl()),
        			'class' => 'delete',
        			'area' => 'adminhtml'
        			]
        	);
        }

        $this->addChild('setForm', 'Ced\CsVendorProductAttribute\Block\Product\Attribute\Set\Main\Formset');
        return parent::_prepareLayout();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getHeader()
    {
        $id = $this->getRequest()->getParam('id');
		return $id ? __('Edit Attribute Set') : __('Add New Attribute Set');
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('setForm');
    }

    /**
     * Return id of form, used by this block
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->getChildBlock('setForm')->getForm()->getId();
    }

    /**
     * @return mixed
     */
    public function getDeleteUrl()
    {
    	$id = $this->getRequest()->getParam('id',false);
    	return $this->getUrl('csvendorproductattribute/*/delete',['id'=>$id]);
    }
}
