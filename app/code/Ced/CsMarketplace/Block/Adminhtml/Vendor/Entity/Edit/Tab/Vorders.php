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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab;

/**
 * Class Vorders
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab
 */
class Vorders extends \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid
{

    /**
     * Vorders constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollection,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $vordersFactory, $helperData, $vorders, $vendorCollection, $data);
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $this->setId('vordersGrids_' . $vendor_id);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/vordersgrid', array('_secure' => true, '_current' => true));
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('vendor_id');
        return $this;
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Vorders
     */
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }
}