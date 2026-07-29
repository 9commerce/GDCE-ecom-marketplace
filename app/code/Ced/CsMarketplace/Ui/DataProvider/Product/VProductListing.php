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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ced\CsMarketplace\Model\Session;

/**
 * Class VProductListing
 * @package Ced\CsMarketplace\Ui\DataProvider\Product
 */
class VProductListing extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var
     */
    protected $collection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $vproductsFactory;

    protected $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        StoreManagerInterface $storeManager,
        $name,
        $primaryFieldName,
        $requestFieldName,
        Session $customerSession,
        CollectionFactory $productCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->vproductsFactory = $vproductsFactory;
        $this->request = $request;
        $this->session = $customerSession->getCustomerSession();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $vProducts = $this->vproductsFactory->create();
        $productCollection = $productCollectionFactory->create();
        $vendorId = $this->session->getVendorId();
        $storeId = $this->storeManager->getStore()->getId();

        if ($storeParamId = $this->request->getParam('store')) {
            $websiteId = $this->storeManager->getStore($storeParamId)->getWebsiteId();
            if ($websiteId) {
                if (in_array($websiteId, $vProducts->getAllowedWebsiteIds())) {
                    $storeId = $storeParamId;
                }
            }
        }

        $productCollection->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'left',
            $storeId
        )->joinAttribute(
            'thumbnail',
            'catalog_product/thumbnail',
            'entity_id',
            null,
            'left',
            $storeId
        )->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            $storeId
        );

        $productCollection->joinTable(
            ['vproduct' => 'ced_csmarketplace_vendor_products'],
            'product_id = entity_id',
            [
                'check_status' => 'check_status',
                'qty' => 'qty',
                'price' => 'price'
            ],
            "{{table}}.vendor_id = {$vendorId}",
            'inner'
        );

        $this->collection = $productCollection;
    }

    public function getData()
    {
        $items = $this->getCollection()->getData();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
