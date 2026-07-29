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

namespace Ced\CsOrder\Block\Order\Creditmemo;

class View extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View
{
    /**
     * Retrieve back url
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('order_id')) {
            return $this->getUrl(
                'csorder/vorders/view',
                [
                    'order_id' => $this->getCreditmemo() ? $this->getRequest()->getParam('order_id') : null,
                    'vorder_id' => $this->getCreditmemo() ? $this->getRequest()->getParam('vorder_id') : null,
                    'active_tab' => 'order_creditmemos'
                ]
            );
        } else {
            return $this->getUrl(
                'csorder/creditmemo/index'
            );
        }
    }

    /**
     * Retrieve capture url
     * @return string
     */
    public function getCaptureUrl()
    {
        return $this->getUrl('csorder/*/capture', ['creditmemo_id' => $this->getCreditmemo()->getId()]);
    }

    /**
     * Retrieve void url
     * @return string
     */
    public function getVoidUrl()
    {
        return $this->getUrl('csorder/*/void', ['creditmemo_id' => $this->getCreditmemo()->getId()]);
    }

    /**
     * Retrieve cancel url
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('csorder/*/cancel', ['creditmemo_id' => $this->getCreditmemo()->getId()]);
    }

    /**
     * Retrieve email url
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'csorder/*/email',
            [
                'creditmemo_id' => $this->getCreditmemo()->getId(),
                'order_id' => $this->getCreditmemo()->getOrderId()
            ]
        );
    }

    /**
     * Retrieve print url
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('csorder/*/print', ['creditmemo_id' => $this->getCreditmemo()->getId()]);
    }

    /**
     * @param bool $flag
     * @return $this|View|\Magento\Backend\Block\Widget\Container
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            $url= $this->getUrl('csorder/creditmemo/');

            if ($this->getCreditmemo()->getBackUrl()) {
                $url=$this->getCreditmemo()->getBackUrl();
            }
            $this->buttonList->update(
                'back',
                'onclick',
                'setLocation(\'' . $url . '\')'
            );
        }
        return $this;
    }

    /**
     * Check whether action is allowed
     * @param  string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
