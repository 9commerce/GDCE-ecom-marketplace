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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source;

use Ced\CsMarketplace\Helper\Data;

/**
 * Class LoginPageDesign
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class LoginPageDesign implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['value' => Data::LOGIN_DEFAULT_DESIGN, 'label' => __('Basic')];
        $options[] = ['value' => Data::LOGIN_ADVANCE_DESIGN, 'label' => __('Advanced')];
        return $options;
    }
}
