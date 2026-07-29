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
namespace Ced\CsMarketplace\Block\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
/**
 *  customer orders grid block
 *
 * @api
 * @since 100.0.2
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->vorders = $vordersCollectionFactory;
        $this->resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {   
        parent::_construct();
        $this->setData('area','adminhtml');
        $this->setId('customer_orders_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        
        $collection = $this->_collectionFactory->getReport('sales_order_grid_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'increment_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'grand_total'
        )->addFieldToSelect(
            'order_currency_code'
        )->addFieldToSelect(
            'store_id'
        )->addFieldToSelect(
            'status'
        )->addFieldToSelect(
            'billing_address'
        )->addFieldToSelect(
            'shipping_address'
        )->addFieldToSelect(
            'billing_name'
        )->addFieldToSelect(
            'shipping_name'
        )->addFieldToFilter(
            'customer_id',
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
        $connection = $this->resource->getConnection();
        $vorderCollection = $this->vorders->create()->addFieldToFilter('vendor_id', $this->_coreRegistry->registry('vendor')['entity_id']);
        $collection->addFieldToFilter('entity_id',['in'=>$vorderCollection->getColumnValues('real_order_id')]);
        $collection->getSelect()
            ->joinLeft(
                ['marketplace_table' => $connection->getTableName('ced_csmarketplace_vendor_sales_order')],
                "marketplace_table.real_order_id=main_table.entity_id",
                ['marketplace_order_id'=>'id']
            );
        $collection->getSelect()->group('entity_id');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', ['header' => __('Order #'), 'width' => '100', 'index' => 'increment_id']);

        $this->addColumn('billing_name', ['header' => __('Bill-to Name'), 'index' => 'billing_name']);

        $this->addColumn('shipping_name', ['header' => __('Ship-to Name'), 'index' => 'shipping_name']);

        $this->addColumn('billing_address', ['header' => __('Billing Address'), 'index' => 'billing_address']);

        $this->addColumn('status', ['header' => __('Order Status'), 'index' => 'status']);

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Order Total'),
                'index' => 'grand_total',
                'type' => 'currency',
                'currency' => 'order_currency_code',
                'rate'  => 1
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                ['header' => __('Purchase Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
            );
        }

        

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'csmarketplace/vorders/view',
            ['vorder_id' => $row->getMarketplaceOrderId(), 'customer_id' =>  $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/orders', ['_current' => true]);
    }
}
