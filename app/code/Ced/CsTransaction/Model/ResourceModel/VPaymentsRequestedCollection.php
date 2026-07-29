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

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class VPaymentsRequestedCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * VPaymentsRequestedCollection constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
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
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        $mainTable = 'ced_csmarketplace_vendor_payments_requested',
        $resourceModel = \Ced\CsMarketplace\Model\ResourceModel\Requested::class,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->_eavAttribute = $eavAttribute;
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
        $nameAttributeId = $this->_eavAttribute->getIdByCode('csmarketplace_vendor', 'name');
        $cedVendorVarcharTable = $this->_resource->getTable('ced_csmarketplace_vendor_varchar');

        $this->getSelect()->joinLeft(
            ['vendor_table' => $cedVendorVarcharTable],
            "main_table.vendor_id = vendor_table.entity_id AND vendor_table.attribute_id=$nameAttributeId",
            [
                'vendor_name' => 'vendor_table.value'
            ]
        );
        $this->addFilterToMap('vendor_name', 'vendor_table.value');
        return $this;
    }
}
