<?php
namespace Ced\CsOrder\Plugin;

use Magento\Framework\App\ResourceConnection;
use Ced\CsMarketplace\Model\VordersFactory;

class ShowApprovedOrdersProducts
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var VordersFactory
     */
    private VordersFactory $vordersFactory;

    private $_vendor = null;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        VordersFactory $vordersFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->vordersFactory = $vordersFactory;
    }

    /**
     * Commented as 'ced_csmarketplace_vendor_sales_order' is not 
     * used in getVproductsReportModel which is overrided in 
     * Kogland_Sales
     * 
     * @param \Ced\CsMarketPlace\Helper\Report $subject
     * @param \Magento\Reports\Model\ResourceModel\Product\Sold\Collection $result
     * @return \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
     */
    // public function afterGetVproductsReportModel(
    //     \Ced\CsMarketPlace\Helper\Report $subject,
    //     \Magento\Reports\Model\ResourceModel\Product\Sold\Collection $result
    // ) {
    //     $result->getSelect()->where('`vendor_order_approval` = 1');
    //     return $result;
    // }

    /**
     * @param \Ced\CsMarketPlace\Helper\Report $subject
     * @param $result
     * @return mixed
     */
    public function afterGetVordersReportModel(
        \Ced\CsMarketPlace\Helper\Report $subject,
        $result
    ) {
        if (!is_array($result)) {
            return $result->addFieldToFilter('vendor_order_approval', ['eq' => 1]);
        }
        return $result;
    }

    public function aroundGetChartData(
        \Ced\CsMarketPlace\Helper\Report $subject,
        \Closure $proceed,
        $vendor,
        $type = 'order',
        $range = 'day'
    ) {
        $results = [];
        if ($vendor && $vendor->getId()) {
            $this->_vendor = $vendor;
            switch ($range) {
                default:
                case 'day':
                    for ($i = 0; $i < 24; $i++) {
                        $results[$i] = [
                            'hour' => $i,
                            'total' => 0
                        ];
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[$result['hour']] = [
                            'hour' => $result['hour'],
                            'total' => $result['total']
                        ];
                    }
                    break;

                case 'week':
                    $date_start = strtotime('-' . date('w') . ' days');

                    for ($i = 0; $i < 7; $i++) {
                        $date = date('Y-m-d', $date_start + ($i * 86400));

                        $results[date('w', strtotime($date))] = [
                            'day' => date('D', strtotime($date)),
                            'total' => 0
                        ];
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('w', strtotime($result['created_at']))] = [
                            'day' => date('D', strtotime($result['created_at'])),
                            'total' => $result['total']
                        ];
                    }
                    break;

                case 'month':
                    for ($i = 1; $i <= date('t'); $i++) {
                        $date = date('Y') . '-' . date('m') . '-' . $i;

                        $results[date('j', strtotime($date))] = [
                            'day' => date('d', strtotime($date)),
                            'total' => 0
                        ];
                    }

                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('j', strtotime($result['created_at']))] = [
                            'day' => date('d', strtotime($result['created_at'])),
                            'total' => $result['total']
                        ];
                    }
                    break;
                case 'year':
                    for ($i = 1; $i <= 12; $i++) {
                        $results[$i] = [
                            'month' => date('M', mktime(0, 0, 0, $i)),
                            'total' => 0
                        ];
                    }
                    $model = $this->_getReportModel($type, $range);
                    foreach ($model as $result) {
                        $results[date('n', strtotime($result['created_at']))] = [
                            'month' => date('M', strtotime($result['created_at'])),
                            'total' => $result['total']
                        ];
                    }
                    break;
            }
        }
        return $results;

//        $result = $proceed($model, $range);
    }

    protected function _getReportModel($model = 'order', $range = 'day')
    {
        if ($this->_vendor != null && $this->_vendor && $this->_vendor->getId()) {
            $model = $this->vordersFactory->create();
            $model = $model->getCollection()->addFieldToFilter('vendor_id', $this->_vendor->getId())
                            ->addFieldToFilter('vendor_order_approval', ['eq' => 1]);
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
            return $model && count($model) ? $model->getData() : [];
        }
        return false;
    }
}
