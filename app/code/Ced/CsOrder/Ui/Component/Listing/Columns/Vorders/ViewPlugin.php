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

namespace Ced\CsOrder\Ui\Component\Listing\Columns\Vorders;

use Ced\CsMarketplace\Ui\Component\Listing\Columns\Vorders\View;
use Ced\CsOrder\Helper\Data;

class ViewPlugin
{
    public Data $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }
    const ORDER_VIEW_ROUTE_PATH = 'csorder/vorders/view';

    /**
     * @param View $subject
     * @param $result
     * @return string
     */
    public function afterGetOrderViewRoutePath(
        View $subject,
        $result
    ) {
        if (!$this->helper->isActive()) {
            return View::ORDER_VIEW_ROUTE_PATH;
        }
        return self::ORDER_VIEW_ROUTE_PATH;
    }

    /**
     * @param View $subject
     * @param $result
     * @param $item
     * @return array
     */
    public function afterGetOrderViewRouteParams(
        View $subject,
        $result,
        $item
    ) {
        return [
            'order_id' => $item['real_order_id'],
            'vorder_id' => $item['id']
        ];
    }
}
