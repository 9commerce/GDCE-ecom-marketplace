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

namespace Ced\CsMarketplace\Model\Vendor\Payment\Methods;

/**
 * Class Paypal
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class Paypal extends AbstractModel
{

    /**
     * @var string
     */
    protected $_code = 'vpaypal';

    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $labeltext = __("Start accepting payments via PayPal!");
        $fields['paypal_email'] = [
            'type' => 'text',
            'after_element_html' => '<a href="https://www.paypal.com/in/signin" target="_blank">'
                .$labeltext.'</a><script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") {document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'paypal_email").className = "required-entry validate-email input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'paypal_email").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'paypal_email").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];

        if (isset($fields['active']) && isset($fields['paypal_email'])) {
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            $fields['active']['onchange'] =
                "if(this.value == '1') {
                 document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "paypal_email').className = 'required-entry validate-email input-text';

                 var spanData = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "paypal_email').closest('li').querySelector('span').innerHTML;
                    spanData = '* '+spanData;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "paypal_email').closest('li').querySelector('span').innerHTML = spanData;

                } else {
                 var spanData = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "paypal_email').closest('li').querySelector('span').innerHTML;
                    spanData = spanData.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "paypal_email').closest('li').querySelector('span').innerHTML = spanData;

                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "paypal_email-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "paypal_email-error').remove()) : '';

                document.getElementById('" .
                $this->getCode() . $this->getCodeSeparator() . "paypal_email').className = 'input-text'; } ";
            //phpcs:enable
        }
        return $fields;
    }

    /**
     * Retrieve labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label' :
                return __('PayPal');
            case 'paypal_email' :
                return __('Email Associated with PayPal Merchant Account');
            default :
                return parent::getLabel($key);
        }
    }
}
