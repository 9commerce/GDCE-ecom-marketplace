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

namespace Ced\CsCommission\Block\Adminhtml;

class Commission extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_commission';
        $this->_blockGroup = 'Ced_CsCommission';
        $this->_headerText = __('Category Wise Commission');
        $this->_addButtonLabel = __('Add Commission');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        if ($this->getRequest()->getParam('popup')) {
            return $this->getUrl('*/*/new', ['popup' => true]);
        } else {
            return $this->getUrl('*/*/new');
        }
    }
}
