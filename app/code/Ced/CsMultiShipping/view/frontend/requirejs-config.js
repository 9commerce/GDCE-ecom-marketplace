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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
// var is_multishipping_enable = !window.ced_multishipping_enable;
var is_multishipping_enable = true;
var config = {
    map: {
        '*': {
            'Magento_Checkout/shipping': 'Ced_CsMultiShipping/shipping',
            regionUpdater:   'Magento_Checkout/js/region-updater'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Ced_CsMultiShipping/js/view/shipping-mixin': true
            }
        }
    }
};
