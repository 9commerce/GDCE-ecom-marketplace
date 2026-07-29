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

class VInvoiceCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * VInvoiceCollection constructor.
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
        $mainTable = 'ced_csorder_invoice',
        $resourceModel = \Ced\CsOrder\Model\ResourceModel\Invoice::class,
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
        $salesInvoiceTable = $this->_resource->getTable('sales_invoice');
        $csSalesOrderTable = $this->_resource->getTable('ced_csmarketplace_vendor_sales_order');

        $this->getSelect()->joinLeft(
            ['invoice_table'=>$salesInvoiceTable],
            "main_table.invoice_id = invoice_table.`entity_id`",
            [
                'invoice_increment_id'=>'invoice_table.increment_id',
                'invoice_created_at'=>'invoice_table.created_at',
                'order_id'=>'invoice_table.order_id',
                'grand_total'=>'invoice_table.grand_total',
                'state'=>'invoice_table.state'
            ]
        );

        $this->getSelect()->joinLeft(
            ['order_table'=>$salesOrderGridTable],
            "invoice_table.order_id = order_table.entity_id",
            [
                'order_increment_id'=>'order_table.increment_id',
                'billing_name'=>'order_table.billing_name',
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
        $this->addFilterToMap('grand_total', 'invoice_table.grand_total');
        $this->addFilterToMap('invoice_increment_id', 'invoice_table.increment_id');
        $this->addFilterToMap('invoice_created_at', 'invoice_table.created_at');
        $this->addFilterToMap('order_increment_id', 'order_table.increment_id');
        $this->addFilterToMap('billing_name', 'order_table.billing_name');
        $this->addFilterToMap('order_created_at', 'order_table.created_at');
        $this->addFilterToMap('state', 'invoice_table.state');
        return $this;
    }
}
