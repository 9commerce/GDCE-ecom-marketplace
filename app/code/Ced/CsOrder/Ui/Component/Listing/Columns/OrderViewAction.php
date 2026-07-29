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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Ui\Component\Listing\Columns;

class OrderViewAction
{
    const ORDER_VIEW_ROUTE_PATH = 'csorder/vendororder/view';

    /**
     * @param \Ced\CsMarketplace\Ui\Component\Listing\Columns\OrderViewAction $subject
     * @param $result
     * @return string
     */
    public function afterGetOrderViewRoutePath(
        \Ced\CsMarketplace\Ui\Component\Listing\Columns\OrderViewAction $subject,
        $result
    ) {
        return self::ORDER_VIEW_ROUTE_PATH;
    }

    /**
     * @param \Ced\CsMarketplace\Ui\Component\Listing\Columns\OrderViewAction $subject
     * @param $result
     * @param $item
     * @return array
     */
    public function afterGetOrderViewRouteParams(
        \Ced\CsMarketplace\Ui\Component\Listing\Columns\OrderViewAction $subject,
        $result,
        $item
    ) {
        $vorderId = false;
        if (isset($item['id'])) {
            $vorderId = $item['id'];
        }
        if (!$vorderId) {
            return $result;
        } else {
            return array_merge($result, ['vorder_id' => $vorderId]);
        }
    }
}
