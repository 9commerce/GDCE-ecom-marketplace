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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function (_, Select, uiRegistry) {
    'use strict';

    return Select.extend({
        /**
         * {@inheritdoc}
         */
        onUpdate: function () {
            this._super();

            this.updateAddBeforeForPrice();
        },

        /**
         * {@inheritdoc}
         */
        setInitialValue: function () {
            this._super();

            this.updateAddBeforeForPrice();

            return this;
        },

        /**
         * Update addbefore for price field. Change it to currency or % depends of price_type value.
         */
        updateAddBeforeForPrice: function () {
            var addBefore, currentValue, priceIndex, priceName, uiPrice;

            priceIndex = typeof this.imports.priceIndex == 'undefined' ? 'price' : this.imports.priceIndex;
            priceName = this.parentName + '.' + priceIndex;

            uiPrice = uiRegistry.get(priceName);

            if (uiPrice && uiPrice.addbeforePool) {
                currentValue = this.value();

                uiPrice.addbeforePool.forEach(function (item) {
                    if (item.value === currentValue) {
                        addBefore = item.label;
                    }
                });

                if (typeof addBefore != 'undefined') {
                    uiPrice.addBefore(addBefore);
                }
            }
        }
    });
});
