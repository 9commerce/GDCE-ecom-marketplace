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
use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ResourceConnection;
/**
 * Class Info
 * @package Ced\CsMarketplace\Block\Vendor\Dashboard
 */
class Info extends AbstractBlock
{

    /**
     * @var Vproducts
     */
    protected $_vproducts;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $vproductsCollection;


    protected $_resourceConnection;
    /**
     * Info constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Vproducts $_vproducts
     * @param ObjectManagerInterface $objectManager
     * @param CollectionFactory $vproductsCollection
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Vproducts $_vproducts,
        ObjectManagerInterface $objectManager,
        CollectionFactory $vproductsCollection,
        ResourceConnection $resourceConnection
    )
    {
        $this->_vproducts = $_vproducts;
        $this->_objectManager = $objectManager;
        $this->vproductsCollection = $vproductsCollection;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getHelper($class)
    {
        return $this->_objectManager->get($class);
    }

    /**
     * Get vendor's Products data
     * @return array
     */
    public function getVendorProductsData()
    {
        // Total Pending Products
        $data = ['total' => [], 'action' => []];
        if ($vendorId = $this->getVendorId()) {
            $url = '*/vproducts/';
            $vproducts = $this->_vproducts;
            $pendingProducts = count($vproducts->getVendorProducts(
                Vproducts::PENDING_STATUS,
                $vendorId
            ));
            $approvedProducts = count($vproducts->getVendorProducts(
                Vproducts::APPROVED_STATUS,
                $vendorId)
            );
            $disapprovedProducts = count($vproducts->getVendorProducts(
                Vproducts::NOT_APPROVED_STATUS,
                $vendorId
            ));

            $data['total'][] = $this->getRoundOffValues($pendingProducts);
            $data['total_int'][] = $pendingProducts;
            $data['action'][] = $this->getUrl($url, ['_secure' => true, 'check_status' => 2]);

            $data['total'][] = $this->getRoundOffValues($approvedProducts);
            $data['total_int'][] = $approvedProducts;
            $data['action'][] = $this->getUrl($url, ['_secure' => true, 'check_status' => 1]);

            $data['total'][] = $this->getRoundOffValues($disapprovedProducts);
            $data['total_int'][] = $disapprovedProducts;
            $data['action'][] = $this->getUrl($url, ['_secure' => true, 'check_status' => 0]);
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getVendorProducts()
    {
        $collection = $this->vproductsCollection->create();
        $cataloginventoryStockItem = $this->_resourceConnection->getTableName('cataloginventory_stock_item');
        $collection->getSelect()->joinLeft($cataloginventoryStockItem, "main_table.product_id={$cataloginventoryStockItem}.product_id", ['qty'=> "{$cataloginventoryStockItem}.qty"]);
        $collection->addFieldToFilter('vendor_id', $this->getVendorId())
            ->setOrder('id', 'DESC')->setPageSize(5)->setCurPage(1);
        return $collection;
    }

    /**
     * @param $productCount
     * @return float|string
     */
    protected function getRoundOffValues($productCount) {
        if ($productCount > 1000000000000) {
            $total = round($productCount / 1000000000000, 1) . 'T';
        } elseif ($productCount > 1000000000) {
            $total = round($productCount / 1000000000, 1) . 'B';
        } elseif ($productCount > 1000000) {
            $total = round($productCount / 1000000, 1) . 'M';
        } elseif ($productCount > 1000) {
            $total = round($productCount / 1000, 1) . 'K';
        } else {
            $total = round($productCount);
        }

        return $total;
    }
}
