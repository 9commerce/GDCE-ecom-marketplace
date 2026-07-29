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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model\ResourceModel;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class VCreditmemoCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * VCreditmemoCollection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param Session $customerSession
     * @param string $mainTable
     * @param string $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Session $customerSession,
        $mainTable = 'ced_csorder_creditmemo',
        $resourceModel = \Ced\CsOrder\Model\ResourceModel\Creditmemo::class,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->session = $customerSession->getCustomerSession();
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    /**
     * @return $this|VCreditmemoCollection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $vendorId = $this->session->getVendorId();
        $this->addFieldToFilter('main_table.vendor_id', $vendorId);

        $salesOrderGridTable = $this->_resource->getTable('sales_order_grid');
        $salesCreditmemoTable=$this->_resource->getTable('sales_creditmemo');
        $csSalesOrderTable = $this->_resource->getTable('ced_csmarketplace_vendor_sales_order');

        $this->getSelect()->joinLeft(
            ['creditmemo_table'=>$salesCreditmemoTable],
            "main_table.creditmemo_id = creditmemo_table.`entity_id`",
            [
                'creditmemo_increment_id'=>'creditmemo_table.increment_id',
                'creditmemo_created_at'=>'creditmemo_table.created_at',
                'order_id'=>'creditmemo_table.order_id',
                'base_grand_total'=>'creditmemo_table.base_grand_total',
                'state'=>'creditmemo_table.state',
                'entity_id' => 'creditmemo_table.entity_id'
            ]
        );

        $this->getSelect()->joinLeft(
            ['order_table'=>$salesOrderGridTable],
            "creditmemo_table.order_id = order_table.entity_id",
            [
                'order_increment_id'=>'order_table.increment_id',
                'shipping_name'=>'order_table.shipping_name',
                'order_created_at'=>'order_table.created_at'
            ]
        );

        $this->getSelect()->joinLeft(
            ['cs_order_table' => $csSalesOrderTable],
            "cs_order_table.order_id = order_table.increment_id and cs_order_table.vendor_id = main_table.vendor_id",
            [
                'vorder_id' => 'cs_order_table.id'
            ]
        );

        $this->addFilterToMap('vorder_id', 'cs_order_table.id');
        $this->addFilterToMap('creditmemo_increment_id', 'creditmemo_table.increment_id');
        $this->addFilterToMap('creditmemo_created_at', 'creditmemo_table.created_at');
        $this->addFilterToMap('base_grand_total', 'creditmemo_table.base_grand_total');
        $this->addFilterToMap('state', 'creditmemo_table.state');
        $this->addFilterToMap('order_increment_id', 'order_table.increment_id');
        $this->addFilterToMap('shipping_name', 'order_table.shipping_name');
        $this->addFilterToMap('order_created_at', 'order_table.created_at');

        return $this;
    }
}
