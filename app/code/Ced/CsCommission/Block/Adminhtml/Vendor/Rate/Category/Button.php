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

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Category;

use Magento\Backend\Block\Widget\Button as CoreButton;

class Button extends CoreButton
{
    /**
     * Config of create new attribute
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_config = null;

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId() . 'JsObject';
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        if ($vendor_id) {
            $type = 'default';
            $id = 0;
        } elseif ($website) {
            $type = 'website';
            $id = $website;
        } elseif ($store) {
            $type = 'store';
            $id = $store;
        } else {
            $type = 'default';
            $id = 0;
        }

        $url = $this->getUrl(
            'cscommission/commission/index',
            [
                'popup' => 1,
                'type' => $type,
                'id' => $id,
                'vendor_id' => $vendor_id
            ]
        );

        $this->setId(
            'create_category_wise_commission_button'
        )->setType(
            'button'
        )->setClass(
            'action-add'
        )->setLabel(
            __('Category Wise Commission')
        )->setDataAttribute(
            [
                'mage-init' => [
                    'categoryWiseGrid' => [
                        'url' => $url,
                    ],
                ],
            ]
        );

        $this->getConfig()->setUrl($url);

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve config of new attribute creation
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = new \Magento\Framework\DataObject();
        }

        return $this->_config;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->setCanShow(true);
        $this->_eventManager->dispatch(
            'adminhtml_mealsunite_booking_setting_create_html_before',
            ['block' => $this]
        );
        if (!$this->getCanShow()) {
            return '';
        }

        return parent::_toHtml();
    }
}
