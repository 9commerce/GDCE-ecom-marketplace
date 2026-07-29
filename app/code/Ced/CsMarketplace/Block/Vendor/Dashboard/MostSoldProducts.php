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

namespace Ced\CsMarketplace\Block\Vendor\Dashboard;


use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class MostSoldProducts
 * @package Ced\CsMarketplace\Block\Vendor\Dashboard
 */
class MostSoldProducts extends AbstractBlock
{

    /**
     * @var ResourceConnection
     */
    public $resourceConnection;

    /**
     * @var Product
     */
    protected $_collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * MostSoldProducts constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Product $collectionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Product $collectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @return mixed
     */
    public function getBestSellerProducts()
    {
        $collection = $this->_collectionFactory
            ->getCollection();

        $collection->getSelect()
            ->joinLeft(
                ['item_table' => $this->resourceConnection->getTableName('sales_order_item')],
                'entity_id = item_table.product_id',
                ['qty_ordered' => 'SUM(item_table.qty_ordered)']
            )->where(
                'qty_ordered > 0'
            )->where(
                'item_table.vendor_id = '. $this->getVendorId()
            )->group('entity_id')
            ->order('qty_ordered ' . 'DESC');

        $collection->getSelect()
            ->join(
                ['vendor_table' => $this->resourceConnection->getTableName('ced_csmarketplace_vendor_products')],
                'entity_id = vendor_table.product_id AND vendor_table.vendor_id = ' . $this->getVendorId(),
                [
                    'entity_id' => 'e.entity_id',
                    'vendor_id' => 'vendor_table.vendor_id',
                    'price' => 'vendor_table.price',
                    'name' => 'vendor_table.name'
                ]
            );

        $collection->addAttributeToFilter(
            'status',
            Status::STATUS_ENABLED
        );
        $collection->getSelect()->limit(5);
        return $collection;
    }

    /**
     * Getter for store manager
     * @return StoreManagerInterface
     */
    public function getStoreManager(){
        return $this->_storeManager;
    }

    /**
     * Convert price from current currency to base currency
     */
    public function convertPrice($price){
        return $this->getStoreManager()
                    ->getStore()
                    ->getBaseCurrency()
                    ->format($price, [], false);
    }
}
