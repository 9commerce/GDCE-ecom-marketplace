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

namespace Ced\CsOrder\Block\Order\Shipment;

class Create extends \Magento\Shipping\Block\Adminhtml\Create
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_mode = 'create';

        parent::_construct();

        $this->buttonList->remove('reset');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'csorder/vorders/view',
            [
                'order_id' => $this->getShipment() ? $this->getRequest()->getParam('order_id') : null,
                'vorder_id' => $this->getShipment() ? $this->getRequest()->getParam('vorder_id') : null
            ]
        );
    }
}
