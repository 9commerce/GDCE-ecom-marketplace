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

namespace Ced\CsMarketplace\Model\Vendor\Group;

/**
 * Class AbstractModel
 * @package Ced\CsMarketplace\Model\Vendor\Group
 */
class AbstractModel extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Get the commission setting
     *
     * @param  \Ced\CsMarketplace\Model\Vendor|null $vendor
     * @return boolean
     */
    public function getCommissionSettings($vendor = null)
    {
        return false;
    }
}
