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
 * @category  Ced
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

define([
    'jquery',
    'mage/backend/validation'
], function (jQuery) {
    'use strict';

    return function (config) {
        var _config = jQuery.extend({
            element: null,
            message: '',
            uniqueClass: 'required-unique'
        }, config);

        if (typeof _config.element === 'string') {
            jQuery.validator.addMethod(
                _config.element,

                function (value, element) {
                    var inputs = jQuery(element)
                            .closest('table')
                            .find('.' + _config.uniqueClass + ':visible'),
                        valuesHash = {},
                        isValid = true;

                    inputs.each(function (el) {
                        var inputValue = inputs[el].value;

                        if (typeof valuesHash[inputValue] !== 'undefined') {
                            isValid = false;
                        }
                        valuesHash[inputValue] = el;
                    });

                    return isValid;
                },

                _config.message
            );
        }
    };
});
