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

/* eslint-disable no-undef */
// jscs:disable jsDoc

require([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'loadingPopup',
    'mage/backend/floating-header'
], function (jQuery, confirm) {
    'use strict';

    /**
     * Delete some category
     * This routine get categoryId explicitly, so even if currently selected tree node is out of sync
     * with this form, we surely delete same category in the tree and at backend.
     *
     * @deprecated
     * @see deleteConfirm
     */
    function categoryDelete(url) {
        confirm({
            content: 'Are you sure you want to delete this category?',
            actions: {
                confirm: function () {
                    location.href = url;
                }
            }
        });
    }

    function displayLoadingMask() {
        jQuery('body').loadingPopup();
    }

    window.categoryDelete = categoryDelete;
    window.displayLoadingMask = displayLoadingMask;
});
