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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab;


use Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\Vpayment;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Class Paymentinformation
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab
 */
class Paymentinformation extends Generic implements TabInterface
{

    /**
     * @var null
     */
    protected $_availableMethods = null;

    /**
     * @var Vendor
     */
    protected $_vendor;

    /**
     * @var Data
     */
    protected $_directoryHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var Vpayment
     */
    protected $vPaymentModel;

    /**
     * Paymentinformation constructor.
     * @param Vpayment $vPaymentModel
     * @param \Ced\CsMarketplace\Helper\Data $helper
     * @param CollectionFactory $collection
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Vendor $vendor
     * @param Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        Vpayment $vPaymentModel,
        \Ced\CsMarketplace\Helper\Data $helper,
        CollectionFactory $collection,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Vendor $vendor,
        Data $directoryHelper,
        array $data = []
    )
    {
        $this->collection = $collection;
        $this->helper = $helper;
        $this->_vendor = $vendor;
        $this->_directoryHelper = $directoryHelper;
        $this->vPaymentModel = $vPaymentModel;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) && in_array($params['type'],
            array_keys($this->vPaymentModel->getStates()))
            ? $params['type']: Vpayment::TRANSACTION_TYPE_CREDIT;
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_fields', [
            'legend'=>__('Transaction Information')
        ]);
        $vendorId = $this->getRequest()->getParam('vendor_id',0);
        $base_amount = $this->getRequest()->getPost('total',0);

        $amountDesc = $this->getRequest()->getPost('orders');

        $vendor = $this->_vendor->getCollection()->toOptionArray();
        $ascn = isset($vendor[$vendorId])?$vendor[$vendorId]:'';
        $fieldset->addField('vendor_id', 'hidden', array(
            'name'      => 'vendor_id',
            'value'     => $vendorId,
        ));
        $fieldset->addField('amount_desc', 'hidden', array(
            'name'      => 'amount_desc',
            'value'     => json_encode($amountDesc),
        ));

        $fieldset->addField('test', 'hidden', array(
            'name'      => 'test',
            'label'    =>  __('Test'),
            'after_element_html'    =>'<script type="text/javascript">
                                            require(["jquery"], function($){
                                                 $("#payment_code").change(function () {
                                                     var payment_code = $("#payment_code").val();
                          
                                                   $("#test").val(payment_code)  ;
                                                     });
                                            });
                                      </script>',
        ));


        $fieldset->addField('currency', 'hidden', array(
            'name'      => 'currency',
            'value'     => $this->_directoryHelper->getBaseCurrencyCode(),
        ));
        $fieldset->addField('vendor_name', 'label', array(
            'label' => __('Vendor'),
            'after_element_html' => '<a target="_blank" href="'.$this->getUrl(
                    'csmarketplace/adminhtml_vendor/edit/',
                    array('vendor_id'=>$vendorId, '_secure'=>true)).'" title="'.$ascn.'">'.$ascn.'</a>',
        ));

        $fieldset->addField('base_amount', 'text', array(
            'label'     => __('Amount'),
            'class'     => 'required-entry validate-greater-than-zero',
            'required'  => true,
            'name'      => 'base_amount',
            'value'   => $base_amount,
            'readonly'  => 'readonly',
            'after_element_html' => '<b>['.$this->_directoryHelper->getBaseCurrencyCode().']
</b><small><i>'.__('Readonly field').'</i>.</small>',
        ));

        $fieldset->addField('payment_code', 'select', array(
            'label'     => __('Payment Method'),
            'class'     => 'required-entry',
            'required'  => true,
            'onchange'  => !$type?'vpayment.changePaymentDatail(this)':'vpayment.changePaymentToOther(this)',
            'name'      => 'payment_code',
            'values' => $this->_vendor->getPaymentMethodsArray($vendorId),
            'after_element_html' => '<small id="beneficiary-payment-detail">'.__('Select Payment Method').
                '</small><script type="text/javascript">var vpayment = "'.$this->getUrl(
                    "*/*/getdetail",
                    array("vendor_id"=>$vendorId)).'";</script>',
        ));

        $fieldset->addField('payment_code_other', 'text', array(
            'label'     => 'Description',
            'class'     => 'required-entry',
            'disbaled'  => 'true',
            'name'      => 'payment_code_other',
            'required'  => true,
        ));

        $fieldset->addField('base_fee', 'text', array(
            'label'     => __('Adjustment Amount'),
            'class'     => 'validate-number validate-not-negative-number',
            'required'  => false,
            'name'      => 'base_fee',
            'after_element_html' => '<b>['.$this->_directoryHelper->getBaseCurrencyCode().']</b><small>'.
                __('Enter adjustment amount if any (amount entered will get deducted from vendor\'s pending amount)').
                '</small>',
        ));


        $fieldset->addField('transaction_id', ''.$this->transacionIdFieldType().'', array(
                'label'     => __('Transaction Id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'transaction_id',
                'value' => ''.$this->autoTransactionId().'',
            )
        );


        $fieldset->addField('textarea', 'textarea', array(
            'label'     => __('Notes'),
            'required'  => false,
            'name'      => 'notes',
        ));

        $form->setHtmlIdPrefix('page_');
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}payment_code",
                'payment_code'
            )
                ->addFieldMap(
                    "{$htmlIdPrefix}payment_code_other",
                    'payment_code_other'
                )
                ->addFieldDependence(
                    'payment_code_other',
                    'payment_code',
                    'other'
                )
        );

        return parent::_prepareForm();

    }

    /**
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl('*/*/*', array(
            '_current'  => true,
            '_secure' => true,
            'vendor_id'       => '{{vendor_id}}'
        ));
    }
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('News Info');
    }

    /**
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return __('News Info');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function autoTransactionId()
    {
        $enabled = $this->helper->getStoreConfig('ced_csmarketplace/general/vendor_transaction_id');

        $id = '';

        if ($enabled)
        {
            $entityId = $this->collection->create()->load();
            $id = $entityId->getLastItem()->getEntityId();
            $id = sprintf("%'.09d\n", ++$id);
        }

        return $id;

    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function transacionIdFieldType()
    {
        $enabled = $this->helper->getStoreConfig('ced_csmarketplace/general/vendor_transaction_id');
        $type = 'text';

        if ($enabled)
            $type = 'hidden';
        return $type;
    }
}
