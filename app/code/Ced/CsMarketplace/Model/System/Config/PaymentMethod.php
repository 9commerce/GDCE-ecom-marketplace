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
 * Class PaymentMethod
 * @package Ced\CsMarketplace\Model\System\Config
 */
class PaymentMethod implements ArrayInterface
{
    const OFFLINE = 0;
    const ONLINE = 1;
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::OFFLINE, 'label' => __('Offline')],
            ['value' => self::ONLINE, 'label' => __('Online')]
        ];
        return $options;
    }
}