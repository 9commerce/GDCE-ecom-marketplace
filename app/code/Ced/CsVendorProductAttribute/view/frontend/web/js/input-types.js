/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available throu
 * gh the world-wide-web at this URL:
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
    'mage/translate'
], function ($) {
    'use strict';

    return function (config) {
        $('select#frontend_input').each(function () {
            var select = $(this),
                currentValue = select.find('option:selected').val(),
                compatibleTypes = config.inputTypes,
                enabledTypes = [],
                iterator,
                warning = $('<label>')
                    .hide()
                    .text($.mage.__('These changes affect all related products.'))
                    .addClass('mage-error')
                    .attr({
                        generated: true, for: select.attr('id')
                    }),
                hint = $('<p>')
                    .hide()
                    .addClass('note')
                    .attr({
                        generated: true
                    }),
                hints = config.hints,

                /**
                 * Toggle hint about changes types
                 */
                toggleWarning = function () {
                    if (select.find('option:selected').val() === currentValue) {
                        warning.hide();
                    } else {
                        warning.show();
                    }
                },

                /**
                 * Toggle hint
                 */
                toggleHint = function () {
                    if (typeof hints[select.find('option:selected').val()] !== 'undefined') {
                        select.after(hint.show().text(hints[select.find('option:selected').val()]));
                    } else {
                        hint.hide();
                    }
                },

                /**
                 * Remove unsupported options
                 */
                removeOption = function () {
                    if (!~enabledTypes.indexOf($(this).val())) {
                        $(this).remove();
                    }
                };

            // find enabled types for switching dor current input type
            for (iterator = 0; iterator < compatibleTypes.length; iterator++) {
                if (compatibleTypes[iterator].indexOf(currentValue) >= 0) {
                    enabledTypes = compatibleTypes[iterator];
                }
            }

            // Check current type (allow only compatible types)
            if (~enabledTypes.indexOf(currentValue)) {
                // Enable select and keep only available options (all other will be removed)
                select.removeAttr('disabled').find('option').each(removeOption);
                // Add warning on page and event for show/hide it
                select.after(warning).on('change', toggleWarning);
            }
            //bind hint toggling on change event
            select.on('change', toggleHint);
            //show hint for currently selected value
            toggleHint();
        });
    };
});
