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


namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\UrlInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Information
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab
 */
class Information extends Generic
{

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Timezone
     */
    protected $_timezone;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Timezone $timezone
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        CustomerFactory $customerFactory,
        Context $context,
        Timezone $timezone,
        Registry $registry,
        FormFactory $formFactory,
        Manager $moduleManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
        $this->_moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_timezone = $timezone;
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm(){
        $form = $this->_formFactory->create();
        $this->setForm($form);
        //$customerId = $this->getRequest()->getParam('customer_id');
        $model = $this->_coreRegistry->registry('vendor_data')->getData();
       // $model = $this->customerFactory->create()->load($customerId)->getData();
        $vendor_id = $this->getRequest()->getParam('vendor_id',0);

        $group = $this->getGroup();
        $attributeCollection = $this->getGroupAttributes();

        $fieldset = $form->addFieldset('group_'.$group->getId(), array('legend'=>__($group->getAttributeGroupName())));

        $isGroupAddonOutputEnabled  = $this->_moduleManager->isOutputEnabled('Ced_CsGroup');



        foreach($attributeCollection as $attribute){
            $attributeCode = $attribute->getAttributeCode();

            if(!$isGroupAddonOutputEnabled && $attributeCode =='group'){
                continue;

            }
            if ($attributeCode == "name" && !empty($model['firstname'])) {
                $model['name'] = $model['firstname'];
            }

            $attribute->setStoreId(0);
            $ascn = 0;
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            if ($attribute->getAttributeCode()=="email" || $attribute->getAttributeCode()=="website_id") {
                continue;
            }

            if ($inputType = $attribute->getFrontend()->getInputType()) {
                if($vendor_id && $attribute->getAttributeCode()=="created_at") {
                    $inputType = 'label';
                    if (!empty($model['created_at'])) {

                        $timezone = $this->scopeConfig->getValue(
                            'general/locale/timezone',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );

                        if ($timezone) {
                            $date = new \DateTime($model['created_at']);
                            $date->setTimezone(new \DateTimeZone($timezone));
                            $locale_time = $date->format('Y-m-d H:i:s');
                            $locale_time_hours = date("Y-m-d h:i:s", strtotime($locale_time));
                            $model['created_at'] = $locale_time_hours;
                        }
                    }
                } elseif (!$vendor_id && $attribute->getAttributeCode()=="created_at") {
                    continue;
                }
                if(!isset($model[$attribute->getAttributeCode()]) || (isset($model[$attribute->getAttributeCode()])
                        && !$model[$attribute->getAttributeCode()])){ $model[$attribute->getAttributeCode()] =
                    $attribute->getDefaultValue();  }

                $showNewStatus = false;
                if($inputType == 'boolean') $inputType = 'select';
                if($attribute->getAttributeCode() == 'customer_id' && $vendor_id) {
                    $options = $attribute->getSource()->toOptionArray($model[$attribute->getAttributeCode()]);
                    if(count($options)) {
                        $ascn = isset($options[0]['label'])?$options[0]['label']:0;
                    }
                }

                if($attribute->getAttributeCode() == 'status') {
                    $showNewStatus = true;
                }

                $fieldType = $inputType;
                $rendererClass = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attribute->getAttributeCode();
                    $form->addType($fieldType, $rendererClass);
                }
                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    array(
                        'name'      => "vendor[".$attribute->getAttributeCode()."]",
                        'label'     => $attribute->getStoreLabel()?$attribute->getStoreLabel()
                            :
                            $attribute->getFrontend()->getLabel(),
                        'class'     => $attribute->getFrontend()->getClass(),
                        'required'  => $attribute->getIsRequired(),
                        'note'      => $ascn && $attribute->getAttributeCode() == 'customer_id' && $vendor_id?'':'',
                        $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id?'disabled':'' =>
                            $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id?true:'',
                        $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id?'readonly':'' =>
                            $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id?true:'',
                        $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id?'style':'' =>
                            $ascn && ($attribute->getAttributeCode() == 'customer_id') && $vendor_id ?
                                'display: none;':'',
                        'value'    => $model[$attributeCode],
                    )
                )
                    ->setEntityAttribute($attribute);
                if($ascn && $attribute->getAttributeCode() == 'customer_id' && $vendor_id) {
                    $element->setAfterElementHtml('<a target="_blank" href="'.$this->getUrl('customer/index/edit',
                            array('id'=>$model[$attribute->getAttributeCode()], '_secure'=>true)).'" title="'
                        .$ascn.'">'.$ascn.'</a>');
                }
                else if($attribute->getAttributeCode() == 'shop_url'){
                    $element->setAfterElementHtml(
                        '<span class="note"><small style="font-size: 10px;">
Please enter your Shop URL Key. For example "my-shop-url".</small></span>'
                    );
                }else if($element->getExtType() == 'file') {
                    if ($element->getValue()) {
                        $url = $this->_storeManager->getStore()->getBaseUrl(
                                UrlInterface::URL_TYPE_MEDIA).$element->getValue();
                        $element->setAfterElementHtml('<p><a href="'.$url.'" target="_blank" >'.
                            $element->getLabel().' Download</a></p>');
                    }
                }
                else {
                    $element->setAfterElementHtml('');
                }
                if ($inputType == 'select') {

                    $element->setValues($attribute->getSource()->getAllOptions(false,$showNewStatus));
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(false,$showNewStatus));
                    $element->setCanBeEmpty(true);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getViewFileUrl('images/calendar.gif'));
                    $element->setDateFormat($this->_timezone->getDateFormatWithLongYear());
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }

        return parent::_prepareForm();
    }
}
