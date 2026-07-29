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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

function confirmSetLocation(message, url) {
    require(['Magento_Ui/js/modal/confirm'] , function (confirmation) {
        confirmation({
            title: "",
            content: message,
            actions: {
                confirm: function(){
                    window.location.href = url;
                }
            }
        });
    });
}
