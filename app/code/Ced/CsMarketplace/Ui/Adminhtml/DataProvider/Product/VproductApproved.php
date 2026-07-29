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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Adminhtml\DataProvider\Product;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;

/**
 * Class VOrderListing
 * @package Ced\CsMarketplace\Ui\Adminhtml\DataProvider\Order
 */
class VproductApproved extends AbstractDataProvider
{

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $setCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $product;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * VProductsListing constructor.
     * @param CollectionFactory $collection
     * @param UrlInterface $urlBuilder
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collection,
        UrlInterface $urlBuilder,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data =[]
    ) {
        $this->product = $product;
        $this->setCollection = $setCollection;
        $this->websiteFactory = $websiteFactory;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->urlBuilder = $urlBuilder;
        $this->vendorFactory = $vendorFactory;
        $this->collection = $collection->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * @return array
     */
    public function getData()
    {
        $allowedIds = [];
        $this->collection->addFieldToFilter('check_status', 1);

        foreach ($this->collection as $item) {
            $allowedIds[] = $item->getProductId();
        }

        $vendor_id = $this->request->getParam('vendor_id',0);
        $store = $this->storeManager->getStore();
        $collection = $this->productCollection->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToFilter('entity_id',array('in'=>$allowedIds));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

        if ($store->getId()) {
            $adminStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        } else {
            $collection->addAttributeToSelect('price');
        }

        $collection->joinField('check_status','ced_csmarketplace_vendor_products', 'check_status',
            'product_id=entity_id',null,'left');
        $collection->joinField('vendor_id','ced_csmarketplace_vendor_products', 'vendor_id',
            'product_id=entity_id',null,'left');
        $collection->joinField('reason','ced_csmarketplace_vendor_products', 'reason',
            'product_id=entity_id',null,'left');
        $collection->joinField('website_id','ced_csmarketplace_vendor_products', 'website_id',
            'product_id=entity_id',null,'left');
        $collection->joinField('id','ced_csmarketplace_vendor_products', 'id',
            'product_id=entity_id',null,'left');

        if($vendor_id) {
            $collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
        }

        $collection = $collection->getData();
        $collection = array_filter($collection, function ($var) {
            return ($var['check_status'] == '1');
        });
        $this->websiteFactory = $this->websiteFactory->create();
        $this->setCollection = $this->setCollection->create();
        foreach ($collection as $key => $collectionValues) {
            $attributeSetName = $this->setCollection->addFieldToFilter('attribute_set_id',
                $collectionValues['attribute_set_id'])->getData()[0]['attribute_set_name'];
            $html = '';
            $vendorId = $collectionValues['vendor_id'];
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $url =  $this->urlBuilder->getUrl("csmarketplace/vendor/edit/", array('vendor_id' =>
                $vendorId));
            $target = "target='_blank'";
            $html .= '<a title="Click to view Vendor Details" onClick="setLocation(\''.$url.'\')" 
            href="'.$url.'" "'.$target.'">'.$vendor->getName().'</a>';
            $collection[$key]['vendor_name'] = $html;
            $collection[$key]['website_name'] = $this->websiteFactory->load($collectionValues['website_id'])->getName();
            $collection[$key]['set_name'] = $attributeSetName;
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($collection),
        ];
    }
}
