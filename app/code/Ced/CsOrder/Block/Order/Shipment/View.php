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

class View extends \Magento\Shipping\Block\Adminhtml\View
{
    /**
     * View constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'shipment_id';
        $this->_mode = 'view';
        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
        if (!$this->getShipment()) {
            return;
        }
        if ($this->getShipment()->getId()) {
            $this->buttonList->add(
                'print',
                [
                    'label' => __('Print'),
                    'class' => 'save',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                    ]
            );
        }
    }
    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('order_id')) {
            return $this->getUrl(
                'csorder/vorders/view',
                [
                 'order_id' => $this->getShipment() ? $this->getRequest()->getParam('order_id') : null,
                 'vorder_id' => $this->getShipment() ? $this->getRequest()->getParam('vorder_id') : null,
                 'active_tab' => 'order_shipments'
                 ]
            );
        } else {
            return $this->getUrl(
                'csorder/shipment/index'
            );
        }
    }

    /**
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('csorder/shipment/print', ['shipment_id' => $this->getShipment()->getId()]);
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            $url=$this->getUrl('csorder/shipment/');

            if ($this->getShipment()->getBackUrl()) {
                $url= $this->getShipment()->getBackUrl();
            }
            $this->buttonList->update(
                'back',
                'onclick',
                'setLocation(\'' . $url . '\')'
            );
        }
        return $this;
    }
}
