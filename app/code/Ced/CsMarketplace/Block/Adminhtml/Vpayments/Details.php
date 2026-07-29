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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments;


use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Details
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments
 */
class Details extends Container
{

    /**
     * Details constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->_controller = '';
        parent::__construct($context, $data);
        $this->_headerText = __('Transaction Details');
        $this->removeButton('reset')
            ->removeButton('delete')
            ->removeButton('save');
    }

    /**
     * Initialize form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('form',
            $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vpayments\Details\Form'));
        return $this;
    }
}