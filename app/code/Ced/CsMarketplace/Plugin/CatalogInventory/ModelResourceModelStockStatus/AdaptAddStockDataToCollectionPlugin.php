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

namespace Ced\CsMarketplace\Plugin\CatalogInventory\ModelResourceModelStockStatus;


use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\View\DesignInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;
use Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status\AdaptAddStockDataToCollectionPlugin as MagentoAdaptAddStockDataToCollectionPlugin;

/**
 * Adapt adding stock data to collection for multi stocks.
 */
class AdaptAddStockDataToCollectionPlugin extends MagentoAdaptAddStockDataToCollectionPlugin
{

    /**
     * @var GetStockIdForCurrentWebsite
     */
    protected $getStockIdForCurrentWebsite;

    /**
     * @var AddStockDataToCollection
     */
    protected $addStockDataToCollection;

    /**
     * @var DesignInterface
     */
    private $designInterface;

    /**
     * AdaptAddStockDataToCollectionPlugin constructor.
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddStockDataToCollection $addStockDataToCollection
     * @param DesignInterface $designInterface
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddStockDataToCollection $addStockDataToCollection,
        DesignInterface $designInterface
    ) {
        parent::__construct($getStockIdForCurrentWebsite, $addStockDataToCollection);
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addStockDataToCollection = $addStockDataToCollection;
        $this->designInterface = $designInterface;

    }

    /**
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @return Collection $collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection,
        $isFilterInStock
    ) {
        $theme = $this->designInterface->getDesignTheme();

        if (!(strpos($theme->getCode(), 'Ced/') !== false)) {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            $this->addStockDataToCollection->execute(
                $collection,
                (bool)$isFilterInStock,
                $stockId
            );
        }

        return $collection;
    }
}
