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

namespace Ced\CsMarketplace\Block\Adminhtml\Vorders;


use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory;
use Ced\CsMarketplace\Model\Vorders;
use Ced\CsMarketplace\Model\VordersFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendData;
use Magento\Framework\DataObject;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vorders
 */
class Grid extends Extended
{
    /**
     * @var int
     */
    const STATE_OPEN = 1;

    /**
     * @var int
     */
    const STATE_PAID = 2;

    /**
     * @var int
     */
    const STATE_CANCELED = 3;

    /**
     * @var int
     */
    const ORDER_NEW_STATUS = 1;

    /**
     * @var int
     */
    const STATE_PARTIALLY_PAID = 6;

    /**
     * @var
     */
    protected static $_states;

    /**
     * @var Vorders
     */
    protected $_vordersFactory;

    /**
     * @var Vorders
     */
    protected $_vorders;

    /**
     * @var CollectionFactory
     */
    protected $vendorCollection;

    /**
     * @var Data
     */
    protected $_csMarketplaceHelper;

    /**
     * Grid constructor.
     * @param Context $context
     * @param BackendData $backendHelper
     * @param VordersFactory $vordersFactory
     * @param Data $helperData
     * @param Vorders $vorders
     * @param CollectionFactory $vendorCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendData $backendHelper,
        VordersFactory $vordersFactory,
        Data $helperData,
        Vorders $vorders,
        CollectionFactory $vendorCollection,
        array $data = []
    ) {
        $this->_vordersFactory = $vordersFactory;
        $this->_vorders = $vorders;
        $this->_csMarketplaceHelper = $helperData;
        $this->vendorCollection = $vendorCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $collection = $this->_vordersFactory->create()->getCollection();

        if ($vendor_id) {
            $collection->addFieldToFilter('vendor_id', $vendor_id);
        }
        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $order_total = $this->_csMarketplaceHelper->getTableKey('base_order_total');
        $shop_commission_fee = $this->_csMarketplaceHelper->getTableKey('shop_commission_base_fee');
        $collection
            ->getSelect()
            ->columns(
                [
                    'net_vendor_earn' => new \Zend_Db_Expr(
                        "({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})"
                    )
                ]
            );

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'date',
                'index' => 'created_at',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order Id'),
                'type' => 'text',
                'index' => 'order_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Orderid',

            ]
        );

        $this->addColumn(
            'vendor_id',
            [
                'header' => __('Vendor Name'),
                'type' => 'text',
                'index' => 'vendor_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname',
                'filter_condition_callback' => array($this, '_vendornameFilter'),

            ]
        );

        $this->addColumn(
            'base_order_total',
            [
                'header' => __('G.T'),
                'type' => 'currency',
                'index' => 'base_order_total',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'

            ]
        );
        $this->addColumn(
            'shop_commission_fee',
            [
                'header' => __('Commission Fee'),
                'type' => 'currency',
                'index' => 'shop_commission_base_fee',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'net_vendor_earn',
            [
                'header' => __('Vendor Payment'),
                'type' => 'currency',
                'index' => 'net_vendor_earn',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'filter_condition_callback' => array($this, '_vendorpaymentFilter')
            ]
        );

        $this->addColumn(
            'order_payment_state',
            [
                'header' => __('Order Payment State'),
                'index' => 'order_payment_state',
                'type' => 'options',
                'options' => $this->getStates(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );
        $this->addColumn(
            'payment_state',
            [
                'header' => __('Vendor Payment State'),
                'type' => 'options',
                'options' => $this->_vorders->getStates(),
                'index' => 'payment_state',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Paynow',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    public function getStates()
    {
        if (self::$_states === null) {
            self::$_states = array(
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
                self::STATE_PARTIALLY_PAID => __('Partially Paid'),
            );
        }
        return self::$_states;
    }

    /**
     * After load collection
     *
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @param $collection
     * @param $column
     * @return string
     */
    protected function _vendornameFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $vendorIds = $this->vendorCollection->create()
            ->addAttributeToFilter('name', array('like' => '%' . $column->getFilter()->getValue() . '%'))
            ->getAllIds();

        if (count($vendorIds) > 0) {
            $collection->addFieldToFilter('vendor_id', array('in', $vendorIds));
        }

        return $collection;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _vendorpaymentFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $order_total = $this->_csMarketplaceHelper->getTableKey('order_total');
        $shop_commission_fee = $this->_csMarketplaceHelper->getTableKey('shop_commission_fee');
        if (isset($value['from'])) {
            $collection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) >='" .
                $value['from'] . "'");
        }
        if (isset($value['to'])) {
            $collection->getSelect()->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) <='" .
                $value['to'] . "'");
        }
        return $collection;
    }
}
