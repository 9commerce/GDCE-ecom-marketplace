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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

define(['jquery', "loader", "domReady!"], function ($) {
    "use strict";
    return function reviewFormLoader()
    {
        $("#review-form").on("submit", function () {
            if ($('#review-form').valid()) {
                $('body').loader('show');
            } else {
                $('body').loader('hide');
            }
        });
    }
});

