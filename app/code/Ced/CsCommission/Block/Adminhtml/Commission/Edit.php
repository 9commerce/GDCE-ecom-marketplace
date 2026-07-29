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

namespace Ced\CsCommission\Block\Adminhtml\Commission;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /** edit construct*/
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Ced_CsCommission';
        $this->_controller = 'adminhtml_commission';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->update('delete', 'label', __('Delete'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
            ],
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkmodule_checkmodel')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($this->_coreRegistry->registry('checkmodule_checkmodel')
                ->getTitle()));
        } else {
            return __('New Item');
        }
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRequest()->getParam('popup')) {
            return $this->getUrl('*/*/', ['popup' => true]);
        } else {
            return $this->getUrl('*/*/');
        }
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        if ($this->getRequest()->getParam('popup')) {
            return $this->getUrl('*/*/delete', [$this->_objectId => $this->getRequest()
                ->getParam($this->_objectId), 'popup' => true]);
        } else {
            return $this->getUrl('*/*/delete', [$this->_objectId => $this->getRequest()
                ->getParam($this->_objectId)]);
        }
    }
}
