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
var config = {
    map: {
        '*': {
            csjquery: "Ced_CsMarketplace/dist/js/jquery.min",
            csnoconflict: "Ced_CsMarketplace/js/ced/csmarketplace/noconflict",
            csvendor: "Ced_CsMarketplace/js/ced/csmarketplace/vendor",
            csbootstrap: "Ced_CsMarketplace/bower_components/bootstrap/dist/js/bootstrap",
            metismenu : "Ced_CsMarketplace/bower_components/metisMenu/dist/metisMenu.min",
            csvendorpanel: "Ced_CsMarketplace/dist/js/sb-admin-2",
            checkoutbalance:    'Magento_Customer/js/checkout-balance',
            captcha: 'Magento_Captcha/js/captcha',
            flot: "Ced_CsMarketplace/js/ced/csmarketplace/flot/jquery.flot",
            flotResize: "Ced_CsMarketplace/js/ced/csmarketplace/flot/jquery.flot.resize.min",
            raphael : "Ced_CsMarketplace/bower_components/raphael/raphael-min",
            morrisMin : "Ced_CsMarketplace/js/ced/csmarketplace/morris.min",
            ceddropdown : "Ced_CsMarketplace/js/view/header"    
        }
    },
    deps: [
        "jquery",
        "jquery/ui",
        "jquery/validate",
        "mage/translate"
    ]
};


