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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search;

use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;
use Ced\CsMarketplace\Model\Vorders;
use Ced\CsMarketplace\Model\Vpayment;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search
 */
class Grid extends Extended
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var CollectionFactory
     */
    protected $_vordersCollFactory;

    /**
     * @var Vpayment
     */
    protected $vPaymentModel;

    /**
     * Grid constructor.
     * @param Vpayment $vPaymentModel
     * @param Context $context
     * @param Data $backendHelper
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $vordersCollFactory
     * @param array $data
     */
    public function __construct(
        Vpayment $vPaymentModel,
        Context $context,
        Data $backendHelper,
        ResourceConnection $resourceConnection,
        CollectionFactory $vordersCollFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_resourceConnection = $resourceConnection;
        $this->_vordersCollFactory = $vordersCollFactory;
        $this->vPaymentModel = $vPaymentModel;

        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }

        $this->setId('ced_csmarketplace_order_search_grid123');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        }

        return $this->_storeManager->getStore();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', [
            'block' => 'search_grid', '_current' => true, 'collapse' => null
        ]);
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return $this
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
     * Prepare collection to be displayed in the grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) && in_array($params['type'], array_keys($this->vPaymentModel->getStates())) ?
            $params['type'] :
            Vpayment::TRANSACTION_TYPE_CREDIT;
        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : 0;

        $collection = $this->_vordersCollFactory->create();
        $collection->addFieldToFilter('main_table.vendor_id', array('eq' => $vendorId));

        $collection->addFieldToFilter('main_table.order_payment_state',
            ['eq' => Invoice::STATE_PAID]
        )->addFieldToFilter('main_table.payment_state', [
            'eq' => ($type == Vpayment::TRANSACTION_TYPE_DEBIT ? Vorders::STATE_REFUND : Vorders::STATE_OPEN)
        ]);

        $collection
            ->getSelect()
            ->columns([
                'net_vendor_earn' => new \Zend_Db_Expr(
                    '(main_table.order_total - main_table.shop_commission_fee)'
                )
            ]);
        /*$collection->getSelect()
            ->joinLeft($orderTable, 'main_table.order_id =' . $orderTable . '.increment_id', array('*'));*/


        $this->setCollection($collection);

        return $this;
    }

    /**
     * Prepare columns
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
        $this->addColumn('order_id', array(
            'header' => __('Order ID#'),
            'align' => 'left',
            'index' => 'order_id',
            'filter_index' => 'order_id',
        ));

        $this->addColumn('base_order_total', array(
            'header' => __('G.T. (Base)'),
            'index' => 'base_order_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',

        ));
        $this->addColumn('order_total', array(
            'header' => __('G.T.'),
            'index' => 'order_total',
            'type' => 'currency',
            'currency' => 'currency',
        ));


        $this->addColumn('shop_commission_fee', array(
            'header' => __('Commission Fee'),
            'index' => 'shop_commission_fee',
            'type' => 'currency',
            'currency' => 'currency',

        ));

        $this->addColumn('net_vendor_earn', array(
            'header' => __('Vendor Payment'),
            'index' => 'net_vendor_earn',
            'type' => 'currency',
            'currency' => 'currency',
        ));
        $this->addColumnAfter('relation_id', array(
            'header' => __('Select'),
            'sortable' => false,
            'header_css_class' => 'a-center',
            'inline_css' => 'csmarketplace_relation_id checkbox',
            'index' => 'id',
            'type' => 'checkbox',
            'field_name' => 'in_orders',
            'values' => $this->_getSelectedOrders(),
            'disabled_values' => array(),
            'align' => 'center',
        ), 'net_vendor_earn');
        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedOrders()
    {
        $params = $this->getRequest()->getParams();
        $orderIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : array();
        return $orderIds;
    }
}