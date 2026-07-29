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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Block\Adminhtml\Commission;

use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Catalog\Model\Product;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Ced\CsCommission\Model\ResourceModel\Commission\Collection
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_backendSession = $context->getBackendSession();
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        if ($this->getRequest()->getParam('popup')) {
            return $this->getUrl('cscommission/*/grid', ['_current' => true, 'popup' => true]);
        } else {
            return $this->getUrl('cscommission/*/grid', ['_current' => true]);
        }
    }

    /**
     * @param Product|Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if ($this->getRequest()->getParam('popup')) {
            return $this->getUrl(
                'cscommission/*/edit',
                ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId(), 'popup' => true]
            );
        } else {
            return $this->getUrl(
                'cscommission/*/edit',
                ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
            );
        }
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        if ($this->_backendSession->getCedtype()) {
            $collection->addFieldToFilter('type', $this->_backendSession->getCedtype());
            $collection->addFieldToFilter('type_id', $this->_backendSession->getCedtypeid());
        }

        if ($this->_backendSession->getCedVendorId()) {
            $collection->addFieldToFilter('vendor', ['eq' => $this->_backendSession->getCedVendorId()]);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'category',
            [
                'header' => __('Category'),
                'index' => 'category',
                'class' => 'category',
                'type' => 'options',
                'options' => $this->_getCategoryOptions(),
            ]
        );
        $this->addColumn(
            'vendor',
            [
                'header' => __('Vendor'),
                'index' => 'vendor',
                'class' => 'vendor',
                'type' => 'options',
                'options' => $this->_getVendorOptions(),
            ]
        );
        $this->addColumn(
            'method',
            [
                'header' => __('Method'),
                'index' => 'method',
                'class' => 'method'
            ]
        );
        $this->addColumn(
            'fee',
            [
                'header' => __('Fee'),
                'index' => 'fee',
                'class' => 'fee'
            ]
        );
        $this->addColumn(
            'priority',
            [
                'header' => __('Priority'),
                'index' => 'priority',
                'class' => 'priority'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'date',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Get category options
     *
     * @return array
     */
    protected function _getCategoryOptions()
    {
        $items = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )->load()->getItems();

        $result = [];
        foreach ($items as $item) {
            $result[$item->getEntityId()] = $item->getName() . '-' . $item->getId();
        }

        return $result;
    }

    /**
     * Get vendor options
     *
     * @return array
     */
    protected function _getVendorOptions()
    {
        $items = $this->_vendorFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )->load()->getItems();

        $result = [];
        $result[0] = 'All';
        foreach ($items as $item) {
            $result[$item->getEntityId()] = $item->getName();
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        if ($this->getRequest()->getParam('popup')) {
            $action = $this->getUrl('cscommission/*/massDelete', ['popup' => true]);
        } else {
            $action = $this->getUrl('cscommission/*/massDelete');
        }
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $action,
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }
}
