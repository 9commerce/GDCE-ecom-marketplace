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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class VendorOrderApprovalOptions to set options of vendor order approval.
 */
class VendorOrderApprovalOptions implements OptionSourceInterface
{

    public const MANUAL = 0;
    public const AUTO = 1;

    /**
     * Returning the option values
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::AUTO, 'label' => __('Auto')],
            ['value' => self::MANUAL, 'label' => __('Manual')]
        ];
        return $options;
    }
}
