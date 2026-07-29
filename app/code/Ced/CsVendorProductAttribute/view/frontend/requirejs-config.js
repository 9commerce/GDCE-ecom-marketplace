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
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

var config = {
    map: {
        '*': {
            "Magento_Catalog/js/options": "Ced_CsVendorProductAttribute/js/options",
            "global": "mage/adminhtml/globals",
            "Magento_Catalog/catalog/product/attribute/unique-validate": "Ced_CsVendorProductAttribute/js/unique-validate",
            eavInputTypes: 'Ced_CsVendorProductAttribute/js/input-types'
        }
    }
};
