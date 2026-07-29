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
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProAssign\Block\Adminhtml\Vendor\Products;

use Ced\CsMarketplace\Model\VendorFactory;
use Exception;

/**
 * Class Grid
 * @package Ced\CsProAssign\Block\Adminhtml\Vendor\Products
 */
class Grid extends \Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
{
    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->vproducts = $vproducts;
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
        $vendor_id = $this->_request->getParam('vendor_id', 0);
        $this->setId('proassign_vproductGrids_' . $vendor_id);
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
        return $this->getUrl('csassign/assign/vproductsgrid', ['_secure' => true, '_current' => true]);
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->removeColumn('set_name');

        $this->addColumnAfter(
            'remove',
            [
                'header' => __('Remove'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Remove'),
                        'url' => [
                            'base' => 'csassign/assign/remove/vendor_id/' . $this->_request->getParam('vendor_id', 0)
                        ],
                        'confirm' => __('Are you sure you want to remove this product from vendor?'),
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsProAssign\Block\Adminhtml\Vendor\Products\Renderer\Remove',
                'index' => 'remove',
            ],
            'status'
        );
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
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('csassign/assign/massDelete', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->vproducts->getMassActionArray();

        $this->getMassactionBlock()->addItem('status', [
            'label' => __('Change status'),
            'url' => $this->getUrl('csassign/assign/massStatus/', ['_current' => true, 'vendor_id' => $this->getRequest()->getParam('vendor_id')]),
            'additional' => [
                'visibility' => [
                    'name' => 'status',
                    'type' => 'select',

                    'label' => __('Status'),
                    'default' => '-1',
                    'values' => $statuses,
                ]
            ]
        ]);

        $this->getMassactionBlock()->addItem(
            'remove',
            [
                'label' => __('Remove'),
                'url' => $this->getUrl('csassign/assign/massremove', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }
}
