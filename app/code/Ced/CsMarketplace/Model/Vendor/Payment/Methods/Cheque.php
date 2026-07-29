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
 * Class Cheque
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class Cheque extends AbstractModel
{

    /**
     * @var string
     */
    protected $_code = 'vcheque';

    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['cheque_payee_name'] = [
            'type' => 'text',
            'after_element_html' => '<script type="text/javascript"> setTimeout(\'if(document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() . 'active").value == "1") { document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'cheque_payee_name").className = "required-entry input-text";var spanData = document.getElementById("' .
                $this->getCode() . $this->getCodeSeparator() .
                'cheque_payee_name").closest("li").querySelector("span").innerHTML;spanData = "* "+spanData;document.getElementById("' . $this->getCode() . $this->getCodeSeparator() .
                'cheque_payee_name").closest("li").querySelector("span").innerHTML = spanData;}\',500);</script>'
        ];

        if (isset($fields['active']) && isset($fields['cheque_payee_name'])) {
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            $fields['active']['onchange'] =
                "if(this.value == '1') {
                    var spanData = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "cheque_payee_name').closest('li').querySelector('span').innerHTML;
                    spanData = '* '+spanData;
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "cheque_payee_name').closest('li').querySelector('span').innerHTML = spanData;

                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "cheque_payee_name').className = 'required-entry input-text';
                    }
                 else {
                 var spanData = document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "cheque_payee_name').closest('li').querySelector('span').innerHTML;
                    spanData = spanData.replace('* ', '');
                    document.getElementById('" . $this->getCode() . $this->getCodeSeparator() .
                "cheque_payee_name').closest('li').querySelector('span').innerHTML = spanData;

                 (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "cheque_payee_name-error')) ?
                (document.getElementById('". $this->getCode() . $this->getCodeSeparator() . "cheque_payee_name-error').remove()) : '';

                 document.getElementById('" .
                $this->getCode() . $this->getCodeSeparator() . "cheque_payee_name').className = 'input-text';
                }";
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
                return __('Check/Money Order');
            case 'cheque_payee_name' :
                return __('Cheque Payee Name');
            default :
                return parent::getLabel($key);
        }
    }
}
