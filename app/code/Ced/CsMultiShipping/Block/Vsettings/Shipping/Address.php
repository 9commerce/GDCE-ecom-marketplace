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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Block\Vsettings\Shipping;

use Ced\CsMarketplace\Model\Session;
use Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Address extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @var \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address
     */
    protected $address;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $_vsettingsFactory;

    /**
     * Address constructor.
     * @param \Magento\Framework\Data\Form $form
     * @param \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address $address
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Magento\Framework\Data\Form $form,
        \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address $address,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct(
            $vendorFactory,
            $customerFactory,
            $context,
            $customerSession,
            $urlFactory
        );
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_form = $form;
        $this->address = $address;
        $this->_vsettingsFactory = $vsettingsFactory;
    }

    /**
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @return Address
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock(\Ced\CsMarketplace\Block\Widget\Form\Renderer\Element::class)
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(\Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset\Element::class)
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(\Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset::class)
        );
        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    public function setForm(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->getBaseUrl());
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Form
     */
    public function getFormObject()
    {
        return $this->getForm();
    }

    /**
     * Get form HTML
     * @return string
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            return $this->getForm()->getHtml();
        }
        return '';
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_form;
        $form->setAction($this->getUrl('*/settings/save', ['section' => AbstractModel::SHIPPING_SECTION]))
            ->setId('form-validate')
            ->setMethod('POST')
            ->setEnctype('multipart/form-data')
            ->setUseContainer(true);
        $vendor = $this->getVendor();
        $model = $this->address;

        $code = 'address';
        $fields = $model->getFields();
        if (count($fields) > 0) {
            $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
            $fieldset = $form->addFieldset('csmultishipping_' . $code, ['legend' => $model->getLabel('label')]);
            $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
            foreach ($fields as $id => $field) {
                $key = strtolower(AbstractModel::SHIPPING_SECTION . '/' . $code . '/' . $id);
                $setting = $this->_vsettingsFactory->create()
                    ->loadByField([$key_tmp, $vendor_id_tmp], [$key, (int)$vendor->getId()]);
                $value = '';
                if ($setting) {
                    $value = $setting->getValue();
                }
                $fieldset->addField(
                    $code . $model->getCodeSeparator() . $id,
                    $field['type'] ?? 'text',
                    [
                        strlen($model->getLabel($id)) > 0 ? 'label' : '' => strlen($model->getLabel($id)) > 0 ? $model
                            ->getLabel($id) : '',
                        'value' => $value,
                        'name' => 'groups[' . $code . '][' . $id . ']',
                        isset($field['class']) ? 'class' : '' => $field['class'] ?? '',
                        isset($field['required']) ? 'required' : '' => $field['required'] ?? '',
                        isset($field['onclick']) ? 'onclick' : '' => $field['onclick'] ?? '',
                        isset($field['onchange']) ? 'onchange' : '' => $field['onchange'] ?? '',
                        isset($field['href']) ? 'href' : '' => $field['href'] ?? '',
                        isset($field['target']) ? 'target' : '' => $field['target'] ?? '',
                        isset($field['values']) ? 'values' : '' => $field['values'] ?? '',
                        isset($field['after_element_html']) ?
                            'after_element_html' : '' => isset($field['after_element_html']) ? '<div><small>' .
                            $field['after_element_html'] .
                                '</small></div>' : '',
                    ]
                );
            }
        }
        $this->setForm($form);

        return $this;
    }

    /**
     * This method is called before rendering HTML
     * @return Mage_Adminhtml_Block_Widget_Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRegionId()
    {
        $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
        $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
        $key = strtolower(AbstractModel::SHIPPING_SECTION . '/address/region_id');
        $addressmodel = $this->_vsettingsFactory->create()
            ->loadByField([$key_tmp, $vendor_id_tmp], [$key, (int)$this->getVendor()->getId()]);
        return $addressmodel->getValue();
    }

    /**
     * Set Fieldset to Form
     * @param $attributes
     * @param $fieldset
     * @param array $exclude
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = [])
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var Mage_Eav_Model_Entity_Attribute $attribute */
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            if (($inputType = $attribute->getFrontend()->getInputType())
                && ('media_image' != $inputType)
                && !in_array($attribute->getAttributeCode(), $exclude)
            ) {
                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    [
                        'name' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontend()->getLabel(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'note' => $attribute->getNote(),
                    ]
                )->setEntityAttribute($attribute);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } elseif ($inputType == 'multiselect') {
                    $element->setCanBeEmpty(true);
                    $element->setValues($attribute->getSource()->getAllOptions(false, true));
                } elseif ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setFormat($this->getDateFormat(\IntlDateFormatter::SHORT));
                } elseif ($inputType == 'datetime') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setTime(true);
                    $element->setStyle('width:50%;');
                    $element->setFormat($this->getDateTimeFormat(\IntlDateFormatter::SHORT));
                } elseif ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Retrieve predefined additional element types
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [];
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }

    /**
     * Add new element type
     * @param Varien_Data_Form_Abstract $baseElement
     */
    protected function _addElementTypes(\Magento\Framework\Data\Form\AbstractForm $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }
}
