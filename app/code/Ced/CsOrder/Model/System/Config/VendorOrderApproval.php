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
 * @package   Ced_CsOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class VendorPaymentStatus
 */
class VendorOrderApproval implements ArrayInterface
{
    /**
     * Constants defined for keys of  options array
     */
    public const STATE_PENDING = 0;

    public const STATE_APPROVED = 1;

    public const STATE_DISAPPROVED = 2;

    /**
     * Returning the option values
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::STATE_PENDING, 'label' => __('Pending')],
            ['value' => self::STATE_APPROVED, 'label' => __('Approved')],
            ['value' => self::STATE_DISAPPROVED, 'label' => __('Disapproved')]
        ];
        return $options;
    }
}
