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

namespace Ced\CsMarketplace\Block\Adminhtml\Vproducts;


use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as EavCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vproducts
 */
class Grid extends Extended
{

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var  Vproducts
     */
    protected $_vproducts;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var EavCollectionFactory
     */
    protected $setCollection;

    /**
     * @var string
     */
    protected $vProductFilter = '';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Vproducts $vproducts
     * @param Manager $moduleManager
     * @param Registry $registry
     * @param CollectionFactory $productCollection
     * @param StoreManagerInterface $storeManager
     * @param Type $type
     * @param EavCollectionFactory $setCollection
     * @param Product $product
     * @param WebsiteFactory $websiteFactory
     * @param VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Vproducts $vproducts,
        Manager $moduleManager,
        Registry $registry,
        CollectionFactory $productCollection,
        StoreManagerInterface $storeManager,
        Type $type,
        EavCollectionFactory $setCollection,
        Product $product,
        WebsiteFactory $websiteFactory,
        VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->_vproducts = $vproducts;
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->productCollection = $productCollection;
        $this->storeManager = $storeManager;
        $this->type = $type;
        $this->setCollection = $setCollection;
        $this->product = $product;
        $this->websiteFactory = $websiteFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
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
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $vendor_id = $this->getRequest()->getParam('vendor_id',0);
        $allowedIds = array();
        if ($this->registry->registry('usePendingProductFilter')) {
            $this->vProductFilter = 'pending';
            $vproducts = $this->_vproducts->getVendorProducts(Vproducts::PENDING_STATUS);
            $this->registry->unregister('usePendingProductFilter');
            $this->registry->unregister('useApprovedProductFilter');
        } elseif($this->registry->registry('useApprovedProductFilter') ) {
            $this->vProductFilter = 'approved';
            $vproducts = $this->_vproducts->getVendorProducts(Vproducts::APPROVED_STATUS);
            $this->registry->unregister('useApprovedProductFilter');
            $this->registry->unregister('usePendingProductFilter');
        } else {
            $vproducts = $this->_vproducts->getVendorProducts();
        }

        foreach($vproducts as $vproduct) {
            $allowedIds[] = $vproduct->getProductId();
        }

        $store = $this->_getStore();
        $pCollection = $this->productCollection;
        $collection = $pCollection->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToFilter('entity_id', array('in' => $allowedIds));

        $manager = $this->moduleManager;
        if ($manager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

        $id  = $store->getId();
        if ($id) {
            $collection->addStoreFilter($store);
            $adminStore = Store::DEFAULT_STORE_ID;
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
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
        $collection->joinField(
            'check_status',
            'ced_csmarketplace_vendor_products',
            'check_status',
            'product_id=entity_id',
            null,
            'left'
        );
        $collection->joinField(
            'vendor_id',
            'ced_csmarketplace_vendor_products',
            'vendor_id',
            'product_id=entity_id',
            null,
            'left'
        );
        $collection->joinField(
            'reason',
            'ced_csmarketplace_vendor_products',
            'reason',
            'product_id=entity_id',
            null,
            'left'
        );
        $collection->joinField(
            'website_id',
            'ced_csmarketplace_vendor_products',
            'website_id',
            'product_id=entity_id',
            null,
            'left'
        );

        if ($vendor_id)
            $collection->addFieldToFilter('vendor_id', ['eq' => $vendor_id]);

        $this->setCollection($collection);

        parent::_prepareCollection();


        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );

        $this->addColumn('type_id',
            array(
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->type->getOptionArray(),
            ));
        $sets = $this->setCollection->create()
            ->setEntityTypeFilter($this->product->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header' => __('Attrib. Set Name'),
                'width' => '60px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
            ));

        $this->addColumn('sku',
            array(
                'header' => __('SKU'),
                'index' => 'sku',
            ));
        $store = $this->_getStore();
        $this->addColumn('price',
            array(
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
            ));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header' => __('Qty'),
                    'width' => '50px',
                    'type' => 'number',
                    'index' => 'qty',
                ));
        }


        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'website_id',
                    'type' => 'options',
                    'options' => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites'
                ]
            );
        }

        if (!$this->registry->registry('usePendingProductFilter') &&
            !$this->registry->registry('useApprovedProductFilter')
        ) {
            $this->addColumn('check_status',
                array(
                    'header' => __('Status'),
                    'width' => '70px',
                    'index' => 'check_status',
                    'type' => 'options',
                    'options' => $this->_vproducts->getOptionArray(),
                ));
        }

        $this->addColumn('action',
            array(
                'header' => __('Action'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\Action',
                'index' => 'action',
            ));

        $this->addColumn('reason',
            array(
                'header' => __('Disapproval Reason'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\Reason',
                'index' => 'action',
            ));

        $this->addColumn('view',
            array(
                'header' => __('View'),
                'type' => 'text',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer\View',
                'index' => 'view',
            ));

        return parent::_prepareColumns();
    }

    /**
     * After load collection
     * @param $collection
     * @param DataObject $column
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _vendornameFilter($collection, DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('name', ['like' => $value . '%']);
        $vendor_id = array();
        foreach ($vendors as $_vendor) {
            $vendor_id[] = $_vendor->getId();
        }
        $this->getCollection()->addFieldToFilter('vendor_id', array('in' => $vendor_id));
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        $statuses = $this->_vproducts->getMassActionArray();

        $this->getMassactionBlock()->addItem('status', array(
            'label' => __('Change status'),
            'url' => $this->getUrl('*/*/massStatus/', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',

                    'label' => __('Status'),
                    'default' => '-1',
                    'values' => $statuses,
                )
            )
        ));
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $param = [
            '_secure' => true,
            '_current' => true,
            'vproduct_filter' => $this->vProductFilter
        ];
        return $this->getUrl('*/*/vproductgrid', $param);
    }


    /**
     * @param \Magento\Catalog\Model\Product|DataObject $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        return false;
    }
}
