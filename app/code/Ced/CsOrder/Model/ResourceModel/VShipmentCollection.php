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

class VShipmentCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * VShipmentCollection constructor.
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
        $mainTable = 'ced_csorder_shipment',
        $resourceModel = \Ced\CsOrder\Model\ResourceModel\Shipment::class,
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
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $vendorId = $this->session->getVendorId();
        $this->addFieldToFilter('main_table.vendor_id', $vendorId);

        $salesOrderGridTable = $this->_resource->getTable('sales_order_grid');
        $salesShipmentTable = $this->_resource->getTable('sales_shipment');
        $csSalesOrderTable = $this->_resource->getTable('ced_csmarketplace_vendor_sales_order');

        $this->getSelect()->joinLeft(
            ['shipment_table' => $salesShipmentTable],
            "main_table.shipment_id = shipment_table.`entity_id`",
            [
                'total_qty' => 'shipment_table.total_qty',
                'shipment_increment_id' => 'shipment_table.increment_id',
                'shipment_created_at' => 'shipment_table.created_at',
                'order_id' => 'shipment_table.order_id',
                'entity_id' => 'shipment_table.entity_id'
            ]
        );
        $this->getSelect()->joinLeft(
            ['order_table' => $salesOrderGridTable],
            "shipment_table.order_id = order_table.entity_id",
            [
                'order_increment_id' => 'order_table.increment_id',
                'shipping_name' => 'order_table.shipping_name',
                'order_created_at' => 'order_table.created_at'
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
        $this->addFilterToMap('total_qty', 'shipment_table.total_qty');
        $this->addFilterToMap('shipment_increment_id', 'shipment_table.increment_id');
        $this->addFilterToMap('shipment_created_at', 'shipment_table.created_at');
        $this->addFilterToMap('order_increment_id', 'order_table.increment_id');
        $this->addFilterToMap('shipping_name', 'order_table.shipping_name');
        $this->addFilterToMap('order_created_at', 'order_table.created_at');
        return $this;
    }
}
