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
        defaults: {
        },
        options:{},
        notifications: ko.observableArray([]),
        /**
         * @override
         */
        initialize: function(options) {
            this.options = options;
            var self = this;
            $(window).scroll(function(){
                if($(window).scrollTop() + $(window).height() == $(document).height()) {
                   self.getNotifications();
                }
                
            });
            this.getNotifications();
            return this._super();
        },
        
        currentPage:0,
        pageSize:20,
        allLoaded:false,
        pendingRequest: ko.observable(false),
        getNotifications: function(){
            var self = this;
            if(self.allLoaded)
                return;
            self.pendingRequest(true);
            $.ajax({
                type: 'GET',
                url: self.options.url+'?page='+(self.currentPage+1)+'&size='+self.pageSize,
                success: function(entries) {
                    if(entries.totalRecords<=((self.currentPage+1)*self.pageSize))
                        self.allLoaded=true;
                    ko.utils.arrayForEach(entries.items, function(entry) {
                        self.notifications.push(entry);
                    });
                    self.currentPage++;
                    self.pendingRequest(false);
                },
                error: function() {
                    self.pendingRequest(false);
                },
                dataType: 'json'
            });
        }
    });
});