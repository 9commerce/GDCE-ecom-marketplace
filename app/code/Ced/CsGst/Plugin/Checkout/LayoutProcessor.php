<?php

namespace Ced\CsGst\Plugin\Checkout;
use Ced\CsGst\Helper\Data;
use Magento\Checkout\Block\Checkout\LayoutProcessor as CheckoutLayoutProcessor;
class LayoutProcessor
{
    /**
     * @var mixed
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * @param CheckoutLayoutProcessor $subject
     * @param array $result
     * @return array|mixed
     */
    public function afterProcess(
        CheckoutLayoutProcessor $subject,
        array $result
    ) {
       if($this->helper->moduleEnable()){
        $paymentForms = $result['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['payments-list']['children'];


        $paymentMethodForms = array_keys($paymentForms);
        if (!isset($paymentMethodForms)) {
            return $result;
        }

        foreach ($paymentMethodForms as $paymentMethodForm) {
            $paymentMethodCode = str_replace('-form', '', $paymentMethodForm, $paymentMethodCode);
            $result = $this->filedAddressType($result, 'gstin_number', $paymentMethodForm, $paymentMethodCode);
        }
        $result = $this->filedShippingAddressType($result, 'gstin_number');
       }

        return $result;
    }


    /**
     * Select Address Type
     * @param $result
     * @param $fieldName
     * @param $paymentMethodForm
     * @param $paymentMethodCode
     * @return array
     */
    public function filedAddressType($result, $fieldName, $paymentMethodForm, $paymentMethodCode)
    {

        $field = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'billingAddress' . $paymentMethodCode . '.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'id' => 'gstin_number'
            ],
            'dataScope' => 'billingAddress' . $paymentMethodCode . '.custom_attributes.' . $fieldName,
            'label' => 'GstIn Number',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'visible' => true,
            'validation' => ["min_text_len‌​gth" => 15, "max_text_length" => 15,'validate-alphanum' => true],
            'id' => $fieldName
        ];

        $result
        ['components']
        ['checkout']
        ['children']
        ['steps']
        ['children']
        ['billing-step']
        ['children']
        ['payment']
        ['children']
        ['payments-list']
        ['children']
        [$paymentMethodForm]
        ['children']
        ['form-fields']
        ['children']
        [$fieldName] = $field;
         
        return $result;
    }




    /**
     * Select Address Type
     * @param $result
     * @param $fieldName
     * @param $paymentMethodForm
     * @param $paymentMethodCode
     * @return array
     */
    public function filedShippingAddressType($result, $fieldName)
    {

        $field = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'id' => 'gstin_number'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.' . $fieldName,
            'label' => 'GstIn Number',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'visible' => true,
            'validation' => ["min_text_len‌​gth" => 15, "max_text_length" => 15,'validate-alphanum' => true],
            'id' => $fieldName
        ];

        $result
        ['components']
        ['checkout']
        ['children']
        ['steps']
        ['children']
        ['shipping-step']
        ['children']
        ['shippingAddress']
        ['children']
        ['shipping-address-fieldset']
        ['children']
        [$fieldName] = $field;

        return $result;
    }
}
