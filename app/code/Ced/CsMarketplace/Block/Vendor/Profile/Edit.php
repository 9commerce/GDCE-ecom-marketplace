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

namespace Ced\CsMarketplace\Block\Vendor\Profile;


use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Form as CsMarketplaceForm;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\Vendor\AttributeFactory;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Edit
 * @package Ced\CsMarketplace\Block\Vendor\Profile
 */
class Edit extends AbstractBlock
{

    protected $_ignoreAttributes = ['reason'];

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Timezone
     */
    protected $timezone;

    /**
     * @var
     */
    protected $_form;

    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * Edit constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param AttributeFactory $attributeFactory
     * @param StoreManagerInterface $storeManager
     * @param Timezone $timezone
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        AttributeFactory $attributeFactory,
        StoreManagerInterface $storeManager,
        Timezone $timezone
    ) {
        $this->_formFactory = $formFactory;
        $this->attributeFactory = $attributeFactory;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Get form object
     *
     * @deprecated deprecated since version 1.2
     * @see getForm()
     */
    public function getFormObject()
    {
        return $this->getForm();
    }

    /**
     * Get form object
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Get form HTML
     *
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
     * @return region Id
     */
    public function getRegionId()
    {
        $region = 0;
        $model = $this->getVendorId() ? $this->getVendor()->getData() : [];
        if (isset($model['region_id'])) {
            $region = $model['region_id'];
        }
        return $region;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Element')
        );
        Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset')
        );
        Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset\Element')
        );

        return parent::_prepareLayout();
    }

    /**
     * This method is called before rendering HTML
     *
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare form before rendering HTML
     */
    protected function _prepareForm()
    {
        $vendorformFields = $this->getVendorAttributes();

        $form = $this->_formFactory->create([
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setUseContainer(true);

        $shopUrl = '';
        $model = [];

        if ($this->getVendorId()) {
            $vendorModel = $this->getVendor();
            $model = $vendorModel->getData();
            $shopUrl = $vendorModel->getVendorShopUrl();
        }

        $id = $this->getVendorId();
        foreach ($vendorformFields as $attribute) {
            $ascn = 0;

            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible()) || in_array($attribute->getAttributeCode(), $this->_ignoreAttributes)) {
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
                    CsMarketplaceForm::$VENDOR_FORM_READONLY_ATTRIBUTES)) {
                    continue;
                }

                $fieldType = $inputType;


                $requiredText = "";
                if (strpos($attribute->getFrontend()->getClass(), 'required') !== false) {
                    $requiredText = "*";
                }

                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $form->addType($fieldType, $rendererClass);
                }

                $attrElement = [
                    'name' => "vendor[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getStoreLabel() ? $requiredText . ' ' . $attribute->getStoreLabel() :
                        $requiredText . ' ' . __($attribute->getFrontend()->getLabel()),
                    'class' => $ascn && $attribute->getAttributeCode() == 'shop_url' && $id ? '' :
                        $attribute->getFrontend()->getClass(),
                    'required' => $ascn && $attribute->getAttributeCode() == 'shop_url' && $id ? false :
                        $attribute->getIsRequired(),
                    'value' => $model[$attribute->getAttributeCode()],
                ];

                if ($ascn && in_array($attribute->getAttributeCode(), ['shop_url', 'email']) && $id) {
                    $attrElement['href'] = $shopUrl;
                    $attrElement['target'] = $attribute->getAttributeCode() == 'shop_url' ? '_blank' : '';
                }

                $element = $form->addField(
                    $attribute->getAttributeCode(),
                    $fieldType,
                    $attrElement
                )->setEntityAttribute($attribute);

                if ($element->getExtType() == 'file') {
                    if ($element->getValue()) {
                        $url = $this->_storeManager->getStore()
                                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $element->getValue();
                        $element->setAfterElementHtml(
                            '<p><a href="' . $url . '" target="_blank" >' .
                            $element->getLabel() . ' ' . __("Download") . '</a></p>'
                        );
                    }
                } else {
                    $element->setAfterElementHtml('');
                }

                switch($inputType) {
                    case 'select':
                        $element->setValues($attribute->getSource()->getAllOptions(true, true));
                        break;

                    case 'multiselect':
                        $element->setValues($attribute->getSource()->getAllOptions(false, true));
                        $element->setCanBeEmpty(true);
                        break;

                    case 'date':
                        $element->setImage($this->getSkinUrl('images/calendar.gif'));
                        $element->setDateFormat($this->timezone->getDateFormatWithLongYear());
                        break;

                    case 'multiline':
                        $element->setLineCount($attribute->getMultilineCount());
                        break;
                }
            }
        }

        $form->addField('password-check', 'checkbox',
            array(
                'label' => __("Change Password"),
                'class' => "password-checkbox",
                'name' => "change_password"
            )
        );

        $form->addField('current_password', 'password',
            array(
                'name' => "vendor[current_password]",
                'label' => __("Current Password"),
                'class' => "change_password form-control input-text",
                'value' => '',
            )
        );
        $form->addField('new_password', 'password',
            array(
                'name' => "vendor[new_password]",
                'label' => __("New Password"),
                'class' => "change_password form-control input-text validate-password",
                'value' => '',
            )
        );
        $form->addField('confirm_password', 'password',
            array(
                'name' => "vendor[confirm_password]",
                'label' => __("Confirm Password"),
                'class' => "change_password form-control input-text validate-cpassword",
                'value' => '',
            )
        );

        $this->setForm($form);
        return $this;
    }

    /**
     * Get collection of Vendor Attributes
     */
    public function getVendorAttributes()
    {
        $vendorAttributes = $this->attributeFactory->create()
            ->setStoreId($this->storeManager->getStore(null)->getId())
            ->getCollection()
            ->addFieldToFilter('is_visible', array('gt' => 0))
            ->setOrder('sort_order', 'ASC');

        $this->_eventManager->dispatch(
            'ced_csmarketplace_vendor_edit_attributes_load_after',
            ['vendorattributes' => $vendorAttributes]
        );

        $vendorAttributes->getSelect()->having('vform.is_visible >= 0');

        return $vendorAttributes;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->getBaseUrl());
        return $this;
    }

    /**
     * Initialize form fields values
     * Method will be called after prepareForm and can be used for field values initialization
     */
    protected function _initFormValues()
    {
        return $this;
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
                    array(
                        'name' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontend()->getLabel(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'required' => $attribute->getIsRequired(),
                        'note' => $attribute->getNote(),
                    )
                )->setEntityAttribute($attribute);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(false, true));
                    $element->setCanBeEmpty(true);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setFormat($this->timezone->getDateFormatWithLongYear());
                } else if ($inputType == 'datetime') {
                    $element->setImage($this->getSkinUrl('images/calendar.gif'));
                    $element->setTime(true);
                    $element->setStyle('width:50%;');

                    $element->setFormat($this->timezone->getDateFormatWithLongYear());

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
     * get Additional Description
     * @param $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }
}
