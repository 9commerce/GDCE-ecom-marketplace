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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor;


use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

/**
 * Class Entity
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor
 */
class Entity extends Container
{

    /**
     * @var string
     */
    protected $_template = 'Ced_CsMarketplace::vendor/vendor.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->getAddButtonOptions();
    }

    /**
     * @return void
     */
    protected function getAddButtonOptions()
    {
        $splitButtonOptions = [
            'label' => __('Add New Vendor'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }

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
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()
                ->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid', 'ced.marketplace.vendor.entity')
        );
        return parent::_prepareLayout();
    }
}
