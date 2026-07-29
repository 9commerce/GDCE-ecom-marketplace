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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class Vproducts
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab
 */
class Vproducts extends \Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
{

    /**
     * Vproducts constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param Manager $moduleManager
     * @param Registry $registry
     * @param CollectionFactory $productCollection
     * @param StoreManagerInterface $storeManager
     * @param Type $type
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        Manager $moduleManager,
        Registry $registry,
        CollectionFactory $productCollection,
        StoreManagerInterface $storeManager,
        Type $type,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $vproducts,
            $moduleManager,
            $registry,
            $productCollection,
            $storeManager,
            $type,
            $setCollection,
            $product,
            $websiteFactory,
            $vendorFactory,
            $data
        );
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $this->setId('vproductGrids_' . $vendor_id);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/vproductsgrid', array('_secure' => true, '_current' => true));
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('vendor_id');
        $this->removeColumn('entity_id');
        $this->removeColumn('set_name');
        return $this;
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Vproducts
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

    /**
     * @return $this|\Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Ced_CsMarketplace::grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/vproducts/massBulkDelete', ['vendor_id'=>$this->getRequest()->getParam('vendor_id')]),
            'confirm' => __('Are you sure?')
        ));

        $statuses = $this->_vproducts->getMassActionArray();

        $this->getMassactionBlock()->addItem('status', array(
            'label' => __('Change status'),
            'url' => $this->getUrl('*/vproducts/massChangeStatus/', array('_current' => true, 'vendor_id'=>$this->getRequest()->getParam('vendor_id'))),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Status'),
                    'default' => '-1',
                    'values' => $statuses,
                )
            )
        ));
        return $this;
    }
}
