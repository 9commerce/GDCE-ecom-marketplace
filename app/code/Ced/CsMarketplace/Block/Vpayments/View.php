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

namespace Ced\CsMarketplace\Block\Vpayments;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;


/**
 * Class View
 * @package Ced\CsMarketplace\Block\Vpayments
 */
class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $acl;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @var \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname
     */
    protected $vendorname;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_acl;

    /**
     * View constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form $form
     * @param \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form $form,
        \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname $vendorname,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        CollectionFactory $vsettingfactory
    ) {
        $this->_acl = $acl;
        $this->registry = $registry;
        $this->form = $form;
        $this->vendorname = $vendorname;
        $this->priceCurrency = $priceCurrency;
        $this->timezone = $timezone;
        $this->_vsettingsFactory = $vsettingfactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Get form object
     *
     * @deprecated deprecated since version 1.2
     * @see getForm()
     * @return \Magento\Framework\Data\Form
     */
    public function getFormObject()
    {
        return $this->getForm();
    }

    /**
     * Get form object
     *
     * @return \Magento\Framework\Data\Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Set form object
     * @param \Magento\Framework\Data\Form $form
     * @return View
     */
    public function setForm(\Magento\Framework\Data\Form $form)
    {
        $this->_form = $form;
        $this->_form->setParent($this);
        $this->_form->setBaseUrl($this->getUrl());
        return $this;
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
     * back Link url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', array('_secure' => true, '_nosid' => true));
    }

    /**
     * Preparing global layout You can redefine this method in child classes for changin layout
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        \Magento\Framework\Data\Form::setElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Element')
        );
        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset')
        );
        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Vpayments\View\Element')
        );

        return parent::_prepareLayout();
    }

    /**
     * This method is called before rendering HTML
     * @return \Ced\CsMarketplace\Block\Vendor\AbstractBlock|View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->_prepareForm();
        $this->_initFormValues();
        return parent::_beforeToHtml();
    }

    /**
     * @return View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        list($model, $fieldsets) = $this->loadFields();
        $form = $this->form;

        foreach ($fieldsets as $key => $data) {
            $fieldset = $form->addFieldset($key, array('legend' => $data['legend']));
            foreach ($data['fields'] as $id => $info) {
                if ($info['type'] == 'link') {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'href' => $info['href'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',
                    ]);
                } else {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',

                    ]);
                }
            }
        }
        $this->setForm($form);
        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadFields()
    {
        $model = $this->getVpayment();


        $renderOrderDesc =
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc');

        $renderName = $this->vendorname;

        $vendorSettings = $this->_vsettingsFactory->create()->addFieldToFilter('vendor_id', $model->getVendorId());
        $data = [];
        foreach ($vendorSettings as $vendorSetting) {
            if ($model->getData('payment_code') == 'banktransfer') {
                if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_name')
                    $data['bank'] = $vendorSetting['value'];
                if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_account_number')
                    $data['account'] = $vendorSetting['value'];
                if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_branch_number')
                    $data['branch'] = $vendorSetting['value'];
                if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_account_name')
                    $data['holder'] = $vendorSetting['value'];
                if ($vendorSetting['key'] == 'payment/vbanktransfer/bank_swift_code')
                    $data['ifsc'] = $vendorSetting['value'];
            }
            if ($model->getData('payment_code') == 'cheque') {
                if ($vendorSetting['key'] == 'payment/vcheque/cheque_payee_name')
                    $data['payee'] = $vendorSetting['value'];
            }
        }
        $other = false;
        if ($model->getData('payment_code') == 'banktransfer') {
            $detail = 'Holder : ' . $data['holder'] . '<br>' . 'Bank : ' . $data['bank'] . '<br>' . 'Branch : ' .
                $data['branch'] . '<br>' . 'IFSC : ' . $data['ifsc'] . '<br>' . 'Account No : ' . $data['account'];
        } elseif ($model->getData('payment_code') == 'cheque') {
            $detail = $data['payee'];
        } else {
            $other = true;
            $detail = $model->getData('payment_code_other');
        }

        $vendor = $this->vendorFactory->create()->load($model->getVendorId());
        $fieldsets = [
            'beneficiary_details' => [
                'fields' => [
                    'vendor_id' => [
                        'label' => __('Vendor Name'),
                        'text' => $vendor->getName(),
                        'type' => 'note'
                    ],
                    'payment_code' => [
                        'label' => __('Payment Method'),
                        'type' => 'label',
                        'value' => $model->getData('payment_code')
                    ],
                    'payment_detail' => [
                        'label' => __('Beneficiary Details'),
                        'type' => 'note',
                        'text' => $detail
                    ],
                ],
                'legend' => __('Beneficiary Details')
            ],

            'order_details' => [
                'fields' => [
                    'amount_desc' => [
                        'label' => __('Order Details'),
                        'text' => $renderOrderDesc->render($model),
                        'type' => 'note',
                    ],
                ],
                'legend' => __('Order Details')
            ],

            'payment_details' => array(
                'fields' => array(
                    'transaction_id' => [
                        'label' => __('Transaction ID#'),
                        'type' => 'label',
                        'value' => $model->getData('transaction_id')
                    ],
                    'created_at' => [
                        'label' => __('Transaction Date'),
                        'value' => $model->getData('created_at'),
                        'type' => 'label',
                    ],
                    'payment_method' => [
                        'label' => __('Transaction Mode'),
                        'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                        'type' => 'label',
                    ],
                    'total_shipping_amount' => [
                        'label' => __('Total Shipping Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('total_shipping_amount'),
                            false,
                            2,
                            null, $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'amount' => [
                        'label' => __('Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('amount'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'base_amount' => [
                        'label' => __('Base Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('base_amount'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'fee' => [
                        'label' => __('Adjustment Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('fee'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'base_fee' => [
                        'label' => __('Base Adjustment Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('base_fee'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'net_amount' => [
                        'label' => __('Net Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('net_amount'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'base_net_amount' => [
                        'label' => __('Base Net Amount'),
                        'value' => $this->priceCurrency->format(
                            $model->getData('base_net_amount'),
                            false,
                            2,
                            null,
                            $model->getCurrency()
                        ),
                        'type' => 'label',
                    ],
                    'notes' => [
                        'label' => __('Notes'),
                        'value' => $model->getData('notes'),
                        'type' => 'label',
                    ],
                ),
                'legend' => __('Transaction Details')
            )
        ];

        if(empty($model->getData('notes'))){
            unset($fieldsets['payment_details']['fields']['notes']);
        }

        if($model->getData('created_at')){
            $timezone_date = $this->scopeConfig->getValue(
                'general/locale/timezone',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );

            if ($timezone_date) {
                $date = new \DateTime($model->getData('created_at'));

                $date->setTimezone(new \DateTimeZone($timezone_date));

                $locale_time = $date->format('Y-m-d H:i:s');

                $fieldsets['payment_details']['fields']['created_at']['value'] = $locale_time;
            }

        }

        if ($model->getBaseCurrency() == $model->getCurrency()) {
            unset($fieldsets['payment_details']['fields']['base_amount']);
            unset($fieldsets['payment_details']['fields']['base_fee']);
            unset($fieldsets['payment_details']['fields']['base_net_amount']);
        }

        return array($model, $fieldsets);
    }

    /**
     * @return mixed
     */

    public function getVpayment()
    {
        $payment = $this->registry->registry('current_vpayment');
        return $payment;
    }

    /**
     * @return $this
     */
    protected function _initFormValues()
    {
        return $this;
    }

    /**
     * Set Fieldset to Form
     *
     * @param array $attributesArray attributes that are to be added
     * @param  $fieldSetValues
     * @param array $excludedAttributes attributes that should be skipped
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _setFieldset($attributesArray, $fieldSetValues, $excludedAttributes = [])
    {
        $this->_addElementTypes($fieldSetValues);
        foreach ($attributesArray as $attributeModel) {
            /* @var \Magento\Eav\Model\Entity\Attribute $attributeModel */
            if (!$attributeModel || ($attributeModel->hasIsVisible() && !$attributeModel->getIsVisible()))
                continue;

            if (($inputType = $attributeModel->getFrontend()->getInputType())
                && !in_array($attributeModel->getAttributeCode(), $excludedAttributes)
                && ($inputType != 'media_image')
            ) {
                $fieldType = $inputType;
                $rendererClass = $attributeModel->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType = $inputType . '_' . $attributeModel->getAttributeCode();
                    $fieldSetValues->addType($fieldType, $rendererClass);
                }

                $element = $fieldSetValues->addField(
                    $attributeModel->getAttributeCode(),
                    $fieldType,
                    [
                        'name' => $attributeModel->getAttributeCode(),
                        'label' => $attributeModel->getFrontend()->getLabel(),
                        'class' => $attributeModel->getFrontend()->getClass(),
                        'required' => $attributeModel->getIsRequired(),
                        'note' => $attributeModel->getNote(),
                    ]
                )->setEntityAttribute($attributeModel);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                switch($inputType) {
                    case "select":
                        $element->setValues($attributeModel->getSource()->getAllOptions(true, true));
                        break;

                    case "multiselect":
                        $element->setValues($attributeModel->getSource()->getAllOptions(false, true));
                        $element->setCanBeEmpty(true);
                        break;

                    case "date":
                        $element->setImage($this->getSkinUrl('images/calendar.gif'));
                        $element->setFormat($this->timezone->getDateFormatWithLongYear());
                        break;

                    case "datetime":
                        $element->setImage($this->getSkinUrl('images/calendar.gif'));
                        $element->setStyle('width:50%;');
                        $element->setTime(true);
                        $element->setFormat(
                            $this->timezone->getDateTimeFormat(
                                \Magento\Framework\Stdlib\DateTime\Timezone::FORMAT_TYPE_SHORT
                            )
                        );
                        break;

                    case "multiline":
                        $element->setLineCount($attributeModel->getMultilineCount());
                        break;
                }
            }
        }
    }

    /**
     * Add new element type
     *
     * @param \Magento\Framework\Data\Form\AbstractForm $baseElement
     */
    protected function _addElementTypes($baseElement)
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
        return array();
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getAdditionalElementHtml($element)
    {
        return '';
    }
}
