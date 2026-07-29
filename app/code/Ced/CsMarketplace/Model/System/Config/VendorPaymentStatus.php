<?php
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
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class VendorPaymentStatus
 * @package Ced\CsMarketplace\Model\System\Config
 */
class VendorPaymentStatus implements ArrayInterface
{
    /**#@+
     * Constants defined for keys of  options array
     */
    const STATE_OPEN = 1;

    const STATE_PAID = 2;

    const STATE_CANCELED = 3;

    const STATE_REFUND = 4;

    const STATE_REFUNDED = 5;


    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::STATE_OPEN, 'label' => __('Pending')],
            ['value' => self::STATE_PAID, 'label' => __('Paid')],
            ['value' => self::STATE_CANCELED, 'label' => __('Canceled')],
            // ['value' => self::STATE_REFUND, 'label' => __('Refund')],
            // ['value' => self::STATE_REFUNDED, 'label' => __('Refunded')],
        ];
        return $options;
    }
}
