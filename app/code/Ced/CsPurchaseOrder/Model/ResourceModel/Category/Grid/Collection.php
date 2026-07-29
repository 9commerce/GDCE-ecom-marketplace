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

namespace Ced\CsPurchaseOrder\Model\ResourceModel\Category\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Ui\Component\DataProvider\Document;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Ced\CsPurchaseOrder\Model\ResourceModel\Category\Grid
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $eavAttribute;

    protected $resource;

    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $eavAttribute,
        \Magento\Framework\App\ResourceConnection $resource,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'ced_category_request_quote_vendor_category',
        $resourceModel = \Ced\CsPurchaseOrder\Model\ResourceModel\Category::class
    )
    {
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult|void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $marketplaceVendorVarchar = $this->resource->getTableName('ced_csmarketplace_vendor_varchar');
        $vendorAttributeId = $this->eavAttribute->create()
            ->getIdByCode('csmarketplace_vendor', 'email');

        $this->getSelect()->join(
            $marketplaceVendorVarchar,
            "main_table.vendor_id = {$marketplaceVendorVarchar}.entity_id AND " .
            "{$marketplaceVendorVarchar}.attribute_id = {$vendorAttributeId}",
            ['email' => 'value']
        );

        $this->addFilterToMap('email', "{$marketplaceVendorVarchar}.value");

        return $this;
    }
}
