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

namespace Ced\CsMarketplace\Helper;


/**
 * Class Report
 * @package Ced\CsMarketplace\Helper
 */
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var null
     */
    protected $_vendor = null;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
     */
    protected $soldCollection;

    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $product;

    /**
     * Report constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Reports\Model\ResourceModel\Product\Sold\Collection $soldCollection
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Reports\Model\ResourceModel\Product\Sold\Collection $soldCollection,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product $product
    ) {
        $this->vordersFactory = $vordersFactory;
        $this->orderFactory = $orderFactory;
        $this->soldCollection = $soldCollection;
        $this->typeFactory = $typeFactory;
        $this->resourceConnection = $resourceConnection;
        $this->product = $product;
        parent::__construct($context);
    }


    /**
     * @param $vendor
     * @return array
     */
    public function getTotalOrdersByCountry($vendor)
    {
        $result = [];
        if ($vendor && $vendor->getId()) {
            $model = $this->vordersFactory->create();
            $orders = $model->getCollection()->addFieldToFilter('vendor_id', $vendor->getId());
            foreach ($orders as $order) {
                $countryId = strtolower($order->getShippingCountryCode());
                if (!strlen($countryId)) {
                    $mainOrder = $this->orderFactory->create()->loadByIncrementId($order->getOrderId());
                    if ($mainOrder && $mainOrder->getId()) {
                        $countryId = strtolower($mainOrder->getBillingAddress()->getData('country_id'));
                    }
                }

                if (strlen($countryId)) {
                    if (isset($result[$countryId]['total'])) {
                        $result[$countryId]['total'] += 1;
                    } else {
                        $result[$countryId]['total'] = 1;
                    }
                    if (isset($result[$countryId]['amount'])) {
                        $result[$countryId]['amount'] += (double)$order->getOrderTotal();
                    } else {
                        $result[$countryId]['amount'] = (double)$order->getOrderTotal();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $vendor
     * @param string $type
     * @param string $range
     * @return array
     */
    public function getChartData($vendor, $type = 'order', $range = 'day')
    {
        $results = [];
        if ($vendor && $vendor->getId()) {
            $this->_vendor = $vendor;
            switch ($range) {
                default:
                case 'day':
                    for ($i = 0; $i < 24; $i++) {
                        $results[$i] = array(
                            'hour' => $i,
                            'total' => 0
                        );
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[$result['hour']] = array(
                            'hour' => $result['hour'],
                            'total' => $result['total']
                        );
                    }
                    break;

                case 'week':
                    $date_start = strtotime('-' . date('w') . ' days');

                    for ($i = 0; $i < 7; $i++) {
                        $date = date('Y-m-d', $date_start + ($i * 86400));

                        $results[date('w', strtotime($date))] = array(
                            'day' => date('D', strtotime($date)),
                            'total' => 0
                        );
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('w', strtotime($result['created_at']))] = array(
                            'day' => date('D', strtotime($result['created_at'])),
                            'total' => $result['total']
                        );
                    }
                    break;

                case 'month':
                    for ($i = 1; $i <= date('t'); $i++) {
                        $date = date('Y') . '-' . date('m') . '-' . $i;

                        $results[date('j', strtotime($date))] = array(
                            'day' => date('d', strtotime($date)),
                            'total' => 0
                        );
                    }

                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('j', strtotime($result['created_at']))] = array(
                            'day' => date('d', strtotime($result['created_at'])),
                            'total' => $result['total']
                        );
                    }
                    break;
                case 'year':
                    for ($i = 1; $i <= 12; $i++) {
                        $results[$i] = array(
                            'month' => date('M', mktime(0, 0, 0, $i)),
                            'total' => 0
                        );
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('n', strtotime($result['created_at']))] = array(
                            'month' => date('M', strtotime($result['created_at'])),
                            'total' => $result['total']
                        );
                    }
                    break;
            }
        }
        return $results;
    }

    /**
     * @param string $model
     * @param string $range
     * @return array|bool
     */
    protected function _getReportModel($model = 'order', $range = 'day')
    {
        if ($this->_vendor != null && $this->_vendor && $this->_vendor->getId()) {
            $model = $this->vordersFactory->create();
            $model = $model->getCollection()->addFieldToFilter('vendor_id', $this->_vendor->getId());
            switch ($model) {
                default:
                case 'order' :
                    switch ($range) {
                        default:
                        case 'day'  :
                            $model->getSelect()
                                ->reset('columns')
                                ->columns("COUNT(*) AS total")
                                ->columns("HOUR(ADDTIME(created_at, '5:30:0.000000')) AS hour")
                                ->where("DATE(created_at) = DATE(NOW())")
                                ->group("HOUR(created_at)")
                                ->order("created_at ASC");
                            break;
                        case 'week' :
                            $date_start = strtotime('-' . date('w') . ' days');
                            $model->getSelect()
                                ->reset('columns')
                                ->columns("created_at")
                                ->columns("COUNT(*) AS total")
                                ->where("DATE(created_at) >= DATE('" . date('Y-m-d', $date_start) . "')")
                                ->group("DAYNAME(created_at)");
                            break;
                        case 'month':
                            $model->getSelect()
                                ->reset('columns')
                                ->columns("created_at")
                                ->columns("COUNT(*) AS total")
                                ->where("DATE(created_at) >= '" . date('Y') . '-' . date('m') . '-1' . "'")
                                ->group("DATE(created_at)");
                            break;
                        case 'year' :
                            $model->getSelect()
                                ->reset('columns')
                                ->columns("created_at")
                                ->columns("COUNT(*) AS total")
                                ->where("YEAR(created_at) = YEAR(NOW())")
                                ->group("MONTH(created_at)");
                            break;
                    }
                    break;
                case 'qty'   : //$model = $this->_vendor->getAssociatedOrders();
                case 'sale'  : //$model = $this->_vendor->getAssociatedOrders();
                    break;
            }
            return $model && count($model) ? $model->getData() : array();
        }
        return false;
    }

    /**
     * @param $vendor
     * @param string $range
     * @param $from_date
     * @param $to_date
     * @param int $status
     * @return array|bool
     */
    public function getVordersReportModel(
        $vendor,
        $range = 'day',
        $from_date = '',
        $to_date = '',
        $status = \Ced\CsMarketplace\Model\Vorders::STATE_PAID,
        $websiteId = 1
    ) {
        $this->_vendor = $vendor;
        if ($this->_vendor != null && $this->_vendor && $this->_vendor->getId()) {
            $from_date = date("Y-m-d 00:00:00", strtotime($from_date));
            $to_date = date("Y-m-d 23:59:59", strtotime($to_date));
            $model = $this->vordersFactory->create();
            $model = $model->getCollection()->addFieldToFilter('vendor_id', $this->_vendor->getId());
            if($status!="*") {
                $model->addFieldToFilter('payment_state',$status);
            }
            switch ($range) {
                default:
                    $model = $this->_vendor->getAssociatedOrders();
                    break;
                case 'day'  :
                    $model->getSelect()
                        ->reset('columns')
                        ->columns("DATE(created_at) AS period")
                        ->columns("COUNT(*) AS order_count")
                        ->columns("SUM(product_qty) AS product_qty")
                        ->columns("SUM(base_order_total) as order_total")
                        ->columns("SUM(shop_commission_base_fee) AS commission_fee")
                        ->columns("(SUM(base_order_total) - SUM(shop_commission_base_fee)) AS net_earned")
                        ->where("created_at>='" . $from_date . "'")
                        ->where("created_at<='" . $to_date . "'")
                        ->where("website_id = " . $websiteId)
                        ->group("DATE(created_at)")
                        ->order("created_at ASC");
                    break;
                case 'month':
                    $model->getSelect()
                        ->reset('columns')
                        ->columns("CONCAT(MONTH(created_at),CONCAT('-',YEAR(created_at))) AS period")
                        ->columns("COUNT(*) AS order_count")
                        ->columns("SUM(product_qty) AS product_qty")
                        ->columns("SUM(base_order_total) AS order_total")
                        ->columns("SUM(shop_commission_base_fee) AS commission_fee")
                        ->columns("(SUM(base_order_total) - SUM(shop_commission_base_fee)) AS net_earned")
                        ->where("created_at>='" . $from_date . "'")
                        ->where("created_at<='" . $to_date . "'")
                        ->where("website_id = " . $websiteId)
                        ->group("YEAR(created_at)")
                        ->group("MONTH(created_at)");
                    break;
                case 'year' :
                    $model->getSelect()
                        ->reset('columns')
                        ->columns("YEAR(created_at) AS period")
                        ->columns("COUNT(*) AS order_count")
                        ->columns("SUM(base_order_total) AS order_total")
                        ->columns("SUM(product_qty) AS product_qty")
                        ->columns("SUM(shop_commission_base_fee) AS commission_fee")
                        ->columns("(SUM(base_order_total) - SUM(shop_commission_base_fee)) AS net_earned")
                        ->where("created_at>='" . $from_date . "'")
                        ->where("created_at<='" . $to_date . "'")
                        ->where("website_id = " . $websiteId)
                        ->group("YEAR(created_at)");
                    break;

            }


            return $model && count($model) ? $model : array();
        }
        return false;
    }

    /**
     * @param $vendorId
     * @param string $from_date
     * @param string $to_date
     * @param bool $group
     * @param int $websiteId
     * @return \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
     */
    public function getVproductsReportModel(
        $vendorId,
        $from_date = '',
        $to_date = '',
        $group = true,
        $websiteId = 1
    )
    {
        $ordersCollection = $this->soldCollection;
        $from = $to = '';
        if ($from_date != '' && $to_date != '') {
            $from = date("Y-m-d 00:00:00", strtotime($from_date));
            $to = date("Y-m-d 23:59:59", strtotime($to_date));
        }
        $compositeTypeIds = $this->typeFactory->create()->getCompositeTypes();
        $coreResource = $this->resourceConnection;
        $adapter = $coreResource->getConnection('read');
        $orderTableAliasName = $adapter->quoteIdentifier('order');

        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $adapter->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        $productJoinCondition = [
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = order_items.product_id'
        ];

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
        }

        $ordersCollection->getSelect()->reset()
            ->from(
                ['vendor_sales_order' => $coreResource->getTableName('ced_csmarketplace_vendor_sales_order')],
                []
            )
            ->joinLeft(
                ['order_items' => $coreResource->getTableName('sales_order_item')],
                'order_items.order_id = vendor_sales_order.real_order_id',
                [
                    'ordered_qty' => 'SUM(order_items.qty_ordered)',
                    'order_item_name' => 'order_items.name',
                    'order_item_total_sales' => 'SUM(order_items.base_row_total)',
                    'sku' => 'order_items.sku'
                ]
            )
            ->joinInner(
                ['order' => $coreResource->getTableName('sales_order')],
                implode(' AND ', $orderJoinCondition),
                []
            )
            ->joinLeft(
                ['e' => $this->product->getEntityTable()],
                implode(' AND ', $productJoinCondition),
                [
                    'entity_id' => 'order_items.product_id',
                    'type_id' => 'order_items.product_type',
                ]
            )->joinLeft(
                ['catalog_product_website' => $coreResource->getTableName('catalog_product_website')],
                ' catalog_product_website.product_id = order_items.product_id',
                []
            )
            ->where('catalog_product_website.website_id = ?', $websiteId)
            ->where('parent_item_id IS NULL')
            ->where('vendor_sales_order.vendor_id="' . $vendorId . '"')
            ->where('order_items.vendor_id="' . $vendorId . '"');
        if ($group) {
            $ordersCollection->getSelect()->group('order_items.product_id');
        }
        $ordersCollection->getSelect()->having('SUM(order_items.qty_ordered) > ?', 0);
        return $ordersCollection;
    }

    /**
     * Prepare between sql
     *
     * @param  string $fieldName Field name with table suffix ('created_at' or 'main_table.created_at')
     * @param  string $from
     * @param  string $to
     * @return string Formatted sql string
     */
    protected function _prepareBetweenSql($fieldName, $from, $to)
    {
        $coreResource = $this->resourceConnection;
        $adapter = $coreResource->getConnection('read');
        return sprintf(
            '(%s >= %s AND %s <= %s)',
            $fieldName,
            $adapter->quote($from),
            $fieldName,
            $adapter->quote($to)
        );
    }
}
