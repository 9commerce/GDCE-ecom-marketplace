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
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'mage/translate',

], function (Component, $, ko, _) {
    'use strict';
    return Component.extend({
        options: {},
        loginpagecontent: {},
        defaults: {},

        /**
         * @override
         */
        initialize: function (options) {
            this.options = options;
            var self = this;
            this.loginpagecontent['totalCustomers'] = ko.observable('');
            this.loginpagecontent['totalProducts'] = ko.observable('');
            this.loginpagecontent['totalSellers'] = ko.observable('');
            this.loginpagecontent['story'] = ko.observable('');
            this.loginpagecontent['steps'] = ko.observable('');
            this.loginpagecontent['features'] = ko.observable('');

            this.getInformation();
            return this._super();
        },
        getInformation: function () {
            var self = this;
            $.getJSON(self.options.url, function (data) {
                self.loginpagecontent.totalCustomers(data.total_customers);
                self.loginpagecontent.totalProducts(data.total_products);
                self.loginpagecontent.totalSellers(data.total_sellers);
                self.loginpagecontent.story(data.story);
                self.loginpagecontent.steps(data.steps);
                self.loginpagecontent.features(data.features);
            });
        }
    });
});