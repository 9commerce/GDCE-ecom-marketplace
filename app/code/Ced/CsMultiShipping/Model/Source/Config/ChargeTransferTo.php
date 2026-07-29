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
 * @category    Ced
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

class ChargeTransferTo implements OptionSourceInterface
{
    const TYPE_VENDOR = 'vendor';
    const TYPE_ADMIN = 'admin';

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_VENDOR, 'label' => __("Vendor")],
            ['value' => self::TYPE_ADMIN, 'label' => __("Admin")]
        ];
    }
}
