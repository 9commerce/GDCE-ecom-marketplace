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
 * Class Status
 * @package Ced\Brand\Model\System\Config
 */
class ProductStatus implements ArrayInterface
{
    /**#@+
     * Constants defined for keys of  options array
     */
    const NOT_APPROVED_STATUS = 0;

    const APPROVED_STATUS = 1;

    const PENDING_STATUS = 2;
    /**#@-*/

    /**
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        $options = [
            ['value' => self::APPROVED_STATUS, 'label' => __('Approved')],
            ['value' => self::PENDING_STATUS, 'label' => __('Pending')],
            ['value' => self::NOT_APPROVED_STATUS, 'label' => __('Disapproved')],
        ];
        return $options;
    }
}
