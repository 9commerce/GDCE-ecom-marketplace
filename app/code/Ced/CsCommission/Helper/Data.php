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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @param $price
     * @param int $rate
     * @param string $method
     * @return int|mixed
     */
    public function calculateFee($price, $rate = 0, $method = 'percentage')
    {
        $mainPrice = 0;
        switch ($method) {
            case \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_FIXED:
                $mainPrice = min($price, $rate);
                break;
            case \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_PERCENTAGE:
                $mainPrice = max((($rate * $price) / 100), 0);
                break;
        }
        return $mainPrice;
    }

    /**
     * @param $price
     * @param int $rate
     * @param $qty
     * @param string $method
     * @return float|int|mixed
     */
    public function calculateCommissionFee($price, $rate, $qty, $method = 'percentage')
    {
        $mainPrice = 0;
        switch ($method) {
            case \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_FIXED:
                $mainPrice = ($qty * $rate);
                break;
            case \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_PERCENTAGE:
                $mainPrice = max((($rate * $price) / 100), 0);
                break;
        }
        return $mainPrice;
    }
}
