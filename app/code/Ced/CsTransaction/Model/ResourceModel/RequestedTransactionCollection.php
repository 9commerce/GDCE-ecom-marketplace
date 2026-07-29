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

namespace Ced\CsTransaction\Model\ResourceModel;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class RequestedTransactionCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * RequestedTransactionCollection constructor.
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
        $mainTable = 'ced_cstransaction_vorder_items',
        $resourceModel = \Ced\CsTransaction\Model\ResourceModel\Items::class,
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
        $this->addFieldToFilter('vendor_id', $vendorId);

        $salesOrderTable = $this->_resource->getTable('sales_order');

        $this->getSelect()->joinLeft(
            ['sales_table' => $salesOrderTable],
            "main_table.order_increment_id = sales_table.increment_id",
            [
                'created_at' => 'sales_table.created_at'
            ]
        );

        $this->addFilterToMap('created_at', 'sales_table.created_at');
        return $this;
    }
}
