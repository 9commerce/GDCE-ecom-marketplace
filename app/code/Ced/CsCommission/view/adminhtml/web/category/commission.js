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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    $.widget('mage.categoryWiseGrid', {
        _create: function () {
            if($('#ced_csmarketplace_vpayments_commission_cw_inherit').is(':checked')){
                $('#create_category_wise_commission_button').addClass("disabled");
            }
            this._on({
                'click': '_showPopup'
            });
        },

        _initModal: function () {
            var self = this;

            this.modal = $('<div id="create_new_category_commision"></div>').modal({
                title: $.mage.__('Category Wise Commission'),
                type: 'slide',
                buttons: [],
                opened: function () {
                    $(this).parent().addClass('modal-content-new-attribute');
                    self.iframe = $('<iframe id="create_new_category_commision_container">').attr({
                        src: self._prepareUrl(),
                        frameborder: 0
                    });
                    self.modal.append(self.iframe);
                    self._changeIframeSize();
                },
                closed: function () {
                    var doc = self.iframe.get(0).document;

                    if (doc && $.isFunction(doc.execCommand)) {
                        //IE9 break script loading but not execution on iframe removing
                        doc.execCommand('stop');
                        self.iframe.remove();
                    }
                    self.modal.data('modal').modal.remove();
                }
            });
            if($('#ced_csmarketplace_vpayments_commission_cw_inherit').is(':checked')){
                $('#create_category_wise_commission_button').addClass("disabled");
            }
        },

        _getHeight: function () {
            if (this.modal.data('modal')) {
                var modal = this.modal.data('modal').modal,
                    modalHead = modal.find('header'),
                    modalHeadHeight = modalHead.outerHeight(),
                    modalHeight = modal.outerHeight(),
                    modalContentPadding = this.modal.parent().outerHeight() - this.modal.parent().height();

                return modalHeight - modalHeadHeight - modalContentPadding;
            }
        },

        _getWidth: function () {
            return this.modal.width();
        },

        _changeIframeSize: function () {
            this.modal.parent().outerHeight(this._getHeight());
            this.iframe.outerHeight(this._getHeight());
            this.iframe.outerWidth(this._getWidth());

        },

        _prepareUrl: function () {
            var $inherit = 0 ;
            if($('#ced_csmarketplace_vpayments_commission_cw_inherit').is(':checked'))
                $inherit = 1 ;
            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'inherit=' +$inherit;
        },

        _showPopup: function () {
            this._initModal();
            this.modal.modal('openModal');
        }
    });

    return $.mage.categoryWiseGrid;
});
