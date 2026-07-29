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

namespace Ced\CsMarketplace\Block\Adminhtml;


use Ced\CsMarketplace\Model\Vpayment;

/**
 * Class Vpayments
 * @package Ced\CsMarketplace\Block\Adminhtml
 */
class Vpayments extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @var string
     */
    protected $_template = 'vpayments/vpayments.phtml';

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Prepare button and grid
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->addButton('assign', array(
            'label' => __('Credit Amount'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
            'class' => 'primary',
        ));

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid::class,
                'ced.csmarketplace.vendor.payments.grid'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            '*/*/new'
        );
    }

    /**
     * @return array
     */
    protected function _getAddButtonOptions()
    {
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }
}
