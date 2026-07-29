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

namespace Ced\CsOrder\Block\Order\Invoice;

class Create extends \Magento\Sales\Block\Adminhtml\Order\Invoice\Create
{
    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve back url
     * @return string
     */
    public function getBackUrl()
    {
        $vOrder = $this->_coreRegistry->registry('current_vorder');
        $order = $this->_coreRegistry->registry('current_order');
        return $this->getUrl(
            'csorder/vorders/view',
            [
                'order_id' => $order->getId(),
                'vorder_id' => $vOrder->getId()
            ]
        );
    }
}
