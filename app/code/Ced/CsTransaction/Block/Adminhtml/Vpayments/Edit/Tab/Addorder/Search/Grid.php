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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search;

use Ced\CsMarketplace\Model\Vpayment;

class Grid extends \Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid
{
    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vtItemsCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory
     */
    protected $_vordersCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_csMarketplaceHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $orderHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $_vPaymentFactory;

    /**
     * Grid constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory
     * @param \Ced\CsOrder\Helper\Data $orderHelper
     * @param \Ced\CsMarketplace\Model\ResourceModel\vendor\CollectionFactory $vendorCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vPaymentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory $vordersCollectionFactory,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory,
        \Ced\CsOrder\Helper\Data $orderHelper,
        \Ced\CsMarketplace\Model\ResourceModel\vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\VpaymentFactory $vPaymentFactory,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->_vPaymentFactory = $vPaymentFactory;
        parent::__construct(
            $vtItemsCollectionFactory,
            $vordersCollectionFactory,
            $helperData,
            $invoiceFactory,
            $orderHelper,
            $vendorCollectionFactory,
            $context,
            $backendHelper,
            $data
        );

        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
        $this->setId('ced_csmarketplace_order_search_grid');

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Prepare collection to be displayed in the grid
     * @param false $flag
     * @return Grid
     */
    protected function _prepareCollection($flag = false)
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) && in_array(
            $params['type'],
            array_keys($this->_vPaymentFactory->create()->getStates())
        ) ? $params['type'] : Vpayment::TRANSACTION_TYPE_CREDIT;

        $vendorId = isset($params['vendor_id']) ? $params['vendor_id'] : 0;
        $orderTable = $this->resourceConnection->getTableName('sales_order');
        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
        if ($this->orderHelper->isActive()) {
            $collection = $this->_vtItemsCollectionFactory->create()
                ->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $collection->addFieldToFilter('qty_ready_to_refund', ['gt' => 0]);
            } else {
                $collection->getSelect()
                    ->where('(`qty_ordered` = `qty_ready_to_pay`+`qty_refunded`) AND (qty_ordered !=qty_refunded)');
            }
            $collection->getSelect()
                ->columns(['net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee})")]);
            $collection->getSelect()
                ->joinLeft($orderTable, 'main_table.order_increment_id =' . $orderTable . '.increment_id', ['*']);
            $this->setCollection($collection);
        } else {
            parent::_prepareCollection(true);
        }

        return parent::_prepareCollection(true);
    }

    /**
     * Prepare columns
     * @return $this|\Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid|object
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        if ($this->orderHelper->isActive()) {
            parent::_prepareColumns();
            $this->removeColumn('relation_id');
            $this->removeColumn('vendor_id');
            $this->removeColumn('order_payment_state');
            $this->removeColumn('payment_state');
            $this->removeColumn('action');
            $params = $this->getRequest()->getParams();
            $type = isset($params['type']) && in_array(
                $params['type'],
                array_keys($this->_vPaymentFactory->create()->getStates())
            ) ? $params['type'] : Vpayment::TRANSACTION_TYPE_CREDIT;

            if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
                $this->removeColumn('qty_ready_to_pay');
                $this->removeColumn('qty_paid');
                $this->removeColumn('net_vendor_earn');
            } else {
                $this->removeColumn('qty_ready_to_refund');
                $this->removeColumn('qty_refunded');
                $this->removeColumn('net_vendor_return');
            }

            $this->addColumnAfter('relation_id', [
                'header' => __('Select'),
                'sortable' => false,
                'header_css_class' => 'a-center',
                'inline_css' => 'csmarketplace_relation_id checkbox',
                'index' => 'id',
                'type' => 'checkbox',
                'field_name' => 'in_orders',
                'values' => $this->_getSelectedOrders(),
                'disabled_values' => [],
                'align' => 'center',
            ], 'net_vendor_earn');
        } else {
            $this->addColumn('order_id', [
                'header' => __('Order ID#'),
                'align' => 'left',
                'index' => 'order_id',
                'filter_index' => 'order_id',
            ]);

            $this->addColumn('base_order_total', [
                'header' => __('G.T. (Base)'),
                'index' => 'base_order_total',
                'type' => 'currency',
                'currency' => 'base_currency_code',

            ]);
            $this->addColumn('order_total', [
                'header' => __('G.T.'),
                'index' => 'order_total',
                'type' => 'currency',
                'currency' => 'currency',
            ]);

            $this->addColumn('shop_commission_fee', [
                'header' => __('Commission Fee'),
                'index' => 'shop_commission_fee',
                'type' => 'currency',
                'currency' => 'currency',

            ]);

            $this->addColumn('net_vendor_earn', [
                'header' => __('Vendor Payment'),
                'index' => 'net_vendor_earn',
                'type' => 'currency',
                'currency' => 'currency',
            ]);

            $this->addColumnAfter('relation_id', [
                'header' => __('Select'),
                'sortable' => false,
                'header_css_class' => 'a-center',
                'inline_css' => 'csmarketplace_relation_id checkbox',
                'index' => 'id',
                'type' => 'checkbox',
                'field_name' => 'in_orders',
                'values' => $this->_getSelectedOrders(),
                'disabled_values' => [],
                'align' => 'center',
            ], 'net_vendor_earn');
        }

        return $this;
    }

    /**
     * prepare return url
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', ['block' => 'search_grid', '_current' => true, 'collapse' => null]);
    }

    /**
     * Get selected orders
     * @return array|false|string[]
     */
    protected function _getSelectedOrders()
    {
        $params = $this->getRequest()->getParams();
        $orderIds = isset($params['order_ids']) ? explode(',', trim($params['order_ids'])) : [];
        return $orderIds;
    }

    /**
     * Remove existing column
     * @param string $columnId
     * @return $this|Grid
     */
    public function removeColumn($columnId)
    {
        if ($this->getColumnSet()->getChildBlock($columnId)) {
            $this->getColumnSet()->unsetChild($columnId);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = array_pop($this->getColumnSet()->getChildNames());
            }
        }
        return $this;
    }
}
