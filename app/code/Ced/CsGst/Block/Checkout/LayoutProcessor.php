<?php

namespace Ced\CsGst\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    const ADDRESS_TYPE = 'gstin_number';

    public function process($result)
    {
        $result = $this->getShippingFormFields($result);
        $result = $this->getBillingFormFields($result);
        return $result;
    }

    public function getAdditionalFields($addressType = 'shipping')
    {
        $field[$addressType] = [
            'config' => [
                'id' => self::ADDRESS_TYPE
            ],
            'label' => 'Gstin Number',
            'provider' => 'checkoutProvider',
            'sortOrder' => 900,
            'visible' => true,
            'validation' => ["min_text_len‌​gth" => 15, "max_text_length" => 15,'validate-alphanum' => true],
            'id' => self::ADDRESS_TYPE
        ];

        return $field;
    }

    public function getShippingFormFields($result)
    {
        $customAttributeCode = 'custom_field';
        $customField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'tooltip' => [
                    'description' => 'this is what the field is for',
                ],
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => 'Custom Attribute',
            'provider' => 'checkoutProvider',
            'sortOrder' => 0,
            'validation' => [
                "min_text_len‌​gth" => 15, "max_text_length" => 15,'validate-alphanum' => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => ''
        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$customAttributeCode] = $customField;

        if (isset($result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset'])
        ) {
            $shippingPostcodeFields = $this->getFields('shippingAddress.custom_attributes','shipping');
            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];

            if (isset($shippingFields['street'])) {
                unset($shippingFields['street']['children'][1]['validation']);
                unset($shippingFields['street']['children'][2]['validation']);
            }

            $shippingFields = array_replace_recursive($shippingFields,$shippingPostcodeFields);

            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;

        }
        
        return $result;
    }

    public function getBillingFormFields($result)
    {
        if(isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list'])) {

            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {

                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

                if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }

                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

                $billingPostcodeFields = $this->getFields('billingAddress' . $paymentMethodCode . '.custom_attributes','billing');

                $billingFields = array_replace_recursive($billingFields, $billingPostcodeFields);

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'] = $billingFields;
            }
        }

        return $result;
    }

    public function getFields($scope,$addressType)
    {
        $fields[self::ADDRESS_TYPE] = $this->getField(self::ADDRESS_TYPE, $scope);
        return $fields;
    }

    public function getField($attributeCode,$scope)
    {
        $field = [
            'config' => [
                'template' => 'ui/form/field',
                'customScope' => $scope,

            ],
            'label' => 'Gstin Number',
            'provider' => 'checkoutProvider',
            'dataScope' => $scope . '.'.$attributeCode,
            'sortOrder' => 0,
            'visible' => true,
            'id' => self::ADDRESS_TYPE,
            'validation' => ["min_text_len‌​gth" => 15, "max_text_length" => 15,'validate-alphanum' => true]
        ];
        return $field;
    }
}