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
 * Class Banktransfer
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class Banktransfer extends AbstractModel
{

    /**
     * @var string
     */
    protected $_code = 'vbanktransfer';

    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['bank_name'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_name").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_name").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'bank_name").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];
        $fields['bank_branch_number'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_branch_number").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_branch_number").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'bank_branch_number").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];
        $fields['bank_swift_code'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_swift_code").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_swift_code").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'bank_swift_code").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];
        $fields['bank_account_name'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_account_name").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_account_name").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'bank_account_name").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];
        $fields['bank_account_number'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_account_number").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'bank_account_number").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'bank_account_number").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];


        if (
            isset($fields['active']) && isset($fields['bank_name']) && isset($fields['bank_branch_number']) && isset($fields['bank_swift_code'])
            && isset($fields['bank_account_name']) && isset($fields['bank_account_number'])
        ) {
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            $fields['active']['onchange'] =
                "if(this.value == '1') {
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_name').className = 'required-entry input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_branch_number').className = 'required-entry input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_swift_code').className = 'required-entry input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_account_name').className = 'required-entry input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_account_number').className = 'required-entry input-text';

                var bank_name = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_name').closest('li').querySelector('span').innerHTML;
                    bank_name = '* '+bank_name;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_name').closest('li').querySelector('span').innerHTML = bank_name;

                var bank_branch_number = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_branch_number').closest('li').querySelector('span').innerHTML;
                    bank_branch_number = '* '+bank_branch_number;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_branch_number').closest('li').querySelector('span').innerHTML = bank_branch_number;

                var bank_swift_code = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_swift_code').closest('li').querySelector('span').innerHTML;
                    bank_swift_code = '* '+bank_swift_code;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_swift_code').closest('li').querySelector('span').innerHTML = bank_swift_code;

                var bank_account_name = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_name').closest('li').querySelector('span').innerHTML;
                    bank_account_name = '* '+bank_account_name;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_name').closest('li').querySelector('span').innerHTML = bank_account_name;

                var bank_account_number = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_number').closest('li').querySelector('span').innerHTML;
                    bank_account_number = '* '+bank_account_number;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_number').closest('li').querySelector('span').innerHTML = bank_account_number;

            } else {
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_name').className = 'input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_branch_number').className = 'input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_swift_code').className = 'input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_account_name').className = 'input-text';
                document.getElementById('" . $this->getCode() . $this->getCodeSeparator() . "bank_account_number').className = 'input-text';

                var bank_name = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_name').closest('li').querySelector('span').innerHTML;
                    bank_name = bank_name.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_name').closest('li').querySelector('span').innerHTML = bank_name;
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_name-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_name-error').remove()) : '';

                var bank_branch_number = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_branch_number').closest('li').querySelector('span').innerHTML;
                    bank_branch_number = bank_branch_number.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_branch_number').closest('li').querySelector('span').innerHTML = bank_branch_number;
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_branch_number-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_branch_number-error').remove()) : '';

                var bank_swift_code = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_swift_code').closest('li').querySelector('span').innerHTML;
                    bank_swift_code = bank_swift_code.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_swift_code').closest('li').querySelector('span').innerHTML = bank_swift_code;
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_swift_code-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_swift_code-error').remove()) : '';

                var bank_account_name = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_name').closest('li').querySelector('span').innerHTML;
                    bank_account_name = bank_account_name.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_name').closest('li').querySelector('span').innerHTML = bank_account_name;
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_account_name-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_account_name-error').remove()) : '';

                var bank_account_number = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_number').closest('li').querySelector('span').innerHTML;
                    bank_account_number = bank_account_number.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "bank_account_number').closest('li').querySelector('span').innerHTML = bank_account_number;
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_account_number-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "bank_account_number-error').remove()) : '';
            } ";
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
                return __('Bank Transfer');
            case 'bank_name' :
                return __('Bank Name');
            case 'bank_branch_number' :
                return __('Bank Branch Number');
            case 'bank_swift_code' :
                return __('Bank Swift Code');
            case 'bank_account_name' :
                return __('Bank Account Name');
            case 'bank_account_number' :
                return __('Bank Account Number');
            default :
                return parent::getLabel($key);
        }
    }
}
