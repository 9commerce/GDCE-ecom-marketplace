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

namespace Ced\CsVendorProductAttribute\Block\Product\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;

/**
 * Class Main
 * @package Ced\CsVendorProductAttribute\Block\Product\Attribute\Edit\Tab
 */
class Main extends AbstractMain
{
    /**
     * @var \Ced\CsVendorProductAttribute\Model\Attributeset
     */
    protected $attributeset;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $eavAttribute;

    /** @var \Ced\CsVendorProductAttribute\Model\System\Config\Source\InputtypeFactory  */
    protected $_vProductInputTypeFactory;

    /**
     * Main constructor.
     * @param \Ced\CsVendorProductAttribute\Model\Attributeset $attributeset
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        \Ced\CsVendorProductAttribute\Model\Attributeset $attributeset,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Ced\CsVendorProductAttribute\Model\System\Config\Source\InputtypeFactory $vProductInputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->attributeset = $attributeset;
        $this->httpRequest = $httpRequest;
        $this->eavAttribute = $eavAttribute;
        $this->_vProductInputTypeFactory = $vProductInputTypeFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $eavData,
            $yesnoFactory,
            $inputTypeFactory,
            $propertyLocker,
            $data
        );
    }

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }

    /**
     * Adding product form elements for editing attribute
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fiedsToRemove = ['attribute_code', 'is_unique', 'frontend_class','frontend_input'];

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\AbstractForm $element */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fiedsToRemove[] = $element->getId();
            }
        }
        foreach ($fiedsToRemove as $id) {
            $fieldset->removeField($id);
        }

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_product_attribute_types', ['response' => $response]);
        $_hiddenFields = [];
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }
        $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);

        $this->_eventManager->dispatch('product_attribute_form_build_main_tab', ['form' => $form]);

        $customOptions = $this->getAttributeSetsOptions();

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Catalog Input Type for Store Owner'),
                'title' => __('Catalog Input Type for Store Owner'),
                'value' => 'text',
                'values' => $this->_vProductInputTypeFactory->create()->toOptionArray()
            ]
        );

        $fieldset->addField('attribute_set_ids', 'multiselect', [
            'name' => 'attribute_set_ids',
            'label' => __('Include in Attribute Set'),
            'title' => __('Include in Attribute Set'),
            'note' => __('Include this Attribute in Attribute Sets'),
            'values' => $customOptions,
            'value' => $this->getAttributeSetValue()
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name' => 'sort_order',
            'label' => __('Sort Order'),
            'title' => __('Sort Order'),
            'value' => $this->_coreRegistry->registry('sort_order')
        ]);

        if ($attributeObject->getId()) {
            $form->getElement('frontend_input')->setDisabled(1);
            if (!$attributeObject->getIsUserDefined()) {
                $form->getElement('is_unique')->setDisabled(1);
            }
        }
        //end

        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply'];
    }

    /**
     * @return array
     */
    public function getAttributeSetsOptions()
    {
        return $this->attributeset->getAllowedAttributeSets();
    }

    /**
     * @return mixed
     */
    public function getAttributeSetValue()
    {
        $attr_id = $this->httpRequest->getParam('attribute_id');
        $model = $this->eavAttribute->load($attr_id);
        return $model->getAttributeSetIds();
    }
}
