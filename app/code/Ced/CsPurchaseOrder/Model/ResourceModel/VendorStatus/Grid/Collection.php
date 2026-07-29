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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $helper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $sessionFactory;

    /**
     * Collection constructor.
     * @param \Ced\CsPurchaseOrder\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'ced_category_request_quote',
        $resourceModel = '\Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder'

    )
    {
        $this->sessionFactory = $sessionFactory;
        $this->helper = $helper;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

    }

    /**
     * @return $this|\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult|void
     */
    protected function _initSelect()
    {

        parent::_initSelect();
        $vendorId = $this->sessionFactory->create()->getVendorId();

        $this->getSelect()->join(
            ['thirdTable' => $this->resource->getTableName('ced_category_request_quote_vendors')],
            "`main_table`.`id` = `thirdTable`.`c_quote_id` AND `thirdTable`.`vendor_id` = '" . $vendorId . "'",
            ['vendor_status' => 'thirdTable.vendor_status']);

        $this->addFilterToMap('id', 'main_table.id');
        $this->addFilterToMap('vendor_status', 'thirdTable.vendor_status');
        return $this;

    }

}
