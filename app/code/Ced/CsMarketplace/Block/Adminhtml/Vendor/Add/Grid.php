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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Add;

use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Module\Manager;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Add
 */
class Grid extends Extended
{

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var Collection
     */
    protected $_group;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param CustomerFactory $customerFactory
     * @param Manager $moduleManager
     * @param Collection $group
     * @param Vendor $vendorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CustomerFactory $customerFactory,
        Manager $moduleManager,
        Collection $group,
        Vendor $vendorFactory,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->_group = $group;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/addgrid', ['_current' => true]);
    }

    /**
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/vendor/new',
            ['customer_id' => $row->getEntityId()]
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $vendors =
            $this->_vendorFactory->getCollection()->addAttributeToSelect('customer_id')->getColumnValues('customer_id');
        $collection = $this->_customerFactory->create()->getCollection()
            ->addAttributeToSelect('*');
        if (!empty($vendors)) {
            $collection->addAttributeToFilter('entity_id', array('nin' => $vendors));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn('created_at', [
                'header' => __('Created At'),
                'align' => 'right',
                'index' => 'created_at',
                'type' => 'date'
            ]
        );

        $this->addColumn('firstname', [
                'header' => __('First Name'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'firstname',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn('lastname', [
                'header' => __('Last Name'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'lastname',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn('email', [
                'header' => __('Email'),
                'align' => 'left',
                'index' => 'email',
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );

        $this->addColumn('group', [
                'header' => __('Customer Group'),
                'align' => 'left',
                'index' => 'group_id',
                'type' => 'options',
                'options' => $this->_group->toOptionHash(),
                'header_css_class' => 'col-group',
                'column_css_class' => 'col-group'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
}
