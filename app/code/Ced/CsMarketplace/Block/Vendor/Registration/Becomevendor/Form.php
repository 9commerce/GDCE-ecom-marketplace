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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vendor\Registration\Becomevendor;

use Ced\CsMarketplace\Model\Vendor\AttributeFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class Form
 * @package Ced\CsMarketplace\Block\Vendor\Registration\Becomevendor
 */
class Form extends Template
{

    /**
     * @var FormFactory
     */
    public $_formFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var Timezone
     */
    protected $timezone;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Attribute
     */
    protected $attributeFactory;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $eavConfig
     * @param Timezone $timezone
     * @param AttributeFactory $attributeFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $eavConfig,
        Timezone $timezone,
        AttributeFactory $attributeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        $this->_eavConfig = $eavConfig;
        $this->timezone = $timezone;
        $this->attributeFactory = $attributeFactory;

        if ($this->getVendor()) {
            $vendor = $this->getVendor();
            if ($vendor->getMetaDescription())
                $this->pageConfig->setDescription($vendor->getMetaDescription());
            if ($vendor->getMetaKeywords())
                $this->pageConfig->setKeywords($vendor->getMetaKeywords());
        }
    }


    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor)
            $this->_vendor = $this->_coreRegistry->registry('current_vendor');
        return $this->_vendor;
    }

    /**
     * Get form object
     * @return \Magento\Framework\Data\Form
     * @see getForm()
     * @deprecated deprecated since version 1.2
     */
    public function getFormObject()
    {
        return $this->getForm();
    }

    /**
     * Get form object
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Set form object
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
     * Preparing global layout
     * @return Form|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Element')
        );

        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset')
        );

        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset\Element')
        );

        return parent::_prepareLayout();
    }

    /**
     * This method is called before rendering HTML
     * @return Form|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare form before rendering HTML
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                    'container' => false
                ],
            ]
        );

        $vendorformFields = $this->getRegistrationAttributes();
        $form->setUseContainer(false);

        $model = $this->getVendorId() ? $this->getVendor()->getData() : [];

        foreach ($vendorformFields as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attribute = $this->_eavConfig->getAttribute('csmarketplace_vendor', $attributeCode);
            $attribute->setStoreId($this->_storeManager->getStore()->getId());

            if (!$attribute || ($attribute->hasUseInRegistration() && !$attribute->getUseInRegistration())) {
                continue;
            }

            if ($inputType = $attribute->getFrontend()->getInputType()) {
                if (!isset($model[$attribute->getAttributeCode()]) ||
                    (isset($model[$attribute->getAttributeCode()]) && !$model[$attribute->getAttributeCode()])
                ) {
                    $model[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                }

                if ($inputType == 'boolean') $inputType = 'select';

                if (in_array($attribute->getAttributeCode(),
                    \Ced\CsMarketplace\Model\Form::$VENDOR_REGISTRATION_RESTRICTED_ATTRIBUTES)) {
                    continue;
                }

                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();

                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $form->addType($fieldType, $rendererClass);
                }

                $afterHtmlShopUrl = '<div id="advice-validate-shopurl-ced-shop-url-field" 
                                          class="validation-advice" 
                                          style="display:none;">
                                          Shop Url is not available.
                                     </div>
										<span class="note"><small style="font-size: 10px;">' .
                    __('Please enter your Shop URL Key. For example "my-shop-url".') . '</small></span>
										<div style="clear:both"></div>
										<span style="float:left;" id="ced-csmarketplace-availability" >&nbsp;</span>
										<span style="float:left;" id="ced-csmarketplace-availability-suggestion" >&nbsp;</span>
										<div style="clear:both"></div>';

                $element = $form->addField('ced-' . str_replace('_', '-', $attribute->getAttributeCode()) . '-field',
                    $fieldType,
                    array(
                        'container_id' => 'ced-' . str_replace('_', '-', $attribute->getAttributeCode()),
                        'name' => "vendor[" . $attribute->getAttributeCode() . "]",
                        'class' => 'form-control ' . $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'placeholder' => __($attribute->getStoreLabel()) ? __($attribute->getStoreLabel()) :
                            __($attribute->getFrontend()->getLabel()),
                        'value' => $model[$attribute->getAttributeCode()],
                        'after_element_html' => $attribute->getAttributeCode() == 'shop_url' ? $afterHtmlShopUrl : '',
                    )
                )->setEntityAttribute($attribute);

                $afterElementHtml = '<label class="addafter"><span class="afterelment note">' .
                    __($attribute->getStoreLabel() ? $attribute->getStoreLabel() :
                        $attribute->getFrontend()->getLabel()) . '</span></label>';

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions());
                    $element->setAfterElementHtml($afterElementHtml);
                } else if ($inputType == 'file' || $inputType == 'image') {
                    $element->setAfterElementHtml($afterElementHtml);
                } else if ($inputType == 'textarea') {
                    $afterElementHtml = '<label class="addafter"><span class="afterelment note">' .
                        __($attribute->getStoreLabel() ? $attribute->getStoreLabel() :
                            $attribute->getFrontend()->getLabel()) . '</span></label>';
                    $element->setAfterElementHtml($afterElementHtml);
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions());
                    $element->setCanBeEmpty(true);
                    $element->setAfterElementHtml($afterElementHtml);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getViewFileUrl('images/calendar.gif'));
                    $element->setDateFormat($this->timezone->getDateFormatWithLongYear());
                    $element->setAfterElementHtml($afterElementHtml);
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                    $element->setAfterElementHtml($afterElementHtml);
                }
            }
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegistrationAttributes($storeId = null)
    {
        if ($storeId == null) $storeId = $this->_storeManager->getStore()->getId();

        $attributes = $this->attributeFactory->create()
            ->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('use_in_registration', array('gt' => 0))
            ->setOrder('position_in_registration', 'ASC');

        $this->_eventManager->dispatch('ced_csmarketplace_registration_attributes_load_after',
            array('attributes' => $attributes));

        return $attributes;
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     * @return $this
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributes attributes that are to be added
     * @param $fieldset
     * @param array $exclude attributes that should be skipped
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _setFieldset($attributes, $fieldset, $exclude = array())
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $key => $attribute) {
            /* @var Attribute $attribute */
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }

            if (($inputType = $attribute->getFrontend()->getInputType())
                && !in_array($attribute->getAttributeCode(), $exclude)
                && ('media_image' != $inputType)
            ) {
                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();

                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    [
                        'name' => $attribute->getAttributeCode(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'label' => __($attribute->getFrontend()->getLabel()),
                        'note' => $attribute->getNote(),
                        'required' => $attribute->getIsRequired(),
                    ]
                );

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));
                $element->setEntityAttribute($attribute);

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(false, true));
                    $element->setCanBeEmpty(true);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setFormat($this->timezone->getDateFormatWithLongYear());
                } else if ($inputType == 'datetime') {
                    $element->setImage($this->getViewFileUrl('images/calendar.gif'));
                    $element->setTime(true);
                    $element->setStyle('width:50%;');
                    $element->setFormat(
                        $this->timezone
                            ->getDateTimeFormat(\IntlDateFormatter::SHORT)
                    );
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }
    }

    /**
     * Add new element type
     * @param AbstractForm $baseElement
     */
    protected function _addElementTypes(AbstractForm $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();

        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

    /**
     * Retrieve predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return [];
    }

    /**
     * Enter description here...
     * @param $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }
}
