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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Ui\DataProvider;

class VendorApprovedQuotations extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var array
     */
    protected $addFieldStrategies;

    /**
     * @var array
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Ced\CsPurchaseOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var
     */
    protected $collection;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Ced\CsPurchaseOrder\Helper\Data $helper,
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder\CollectionFactory $collection,
        \Magento\Framework\App\ResourceConnection $resource,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->sessionFactory = $sessionFactory;
        $this->helper = $helper;
        $this->vendorStatusCollection = $vendorStatusCollection;

        $vendorId = $this->sessionFactory->create()->getVendorId();
        $this->collection = $collection->create()
                ->setPageSize(20)
                ->setCurPage(1);
        $this->collection->getSelect()->joinRight(
            ['ca' => $resource->getTableName('ced_category_request_quote_vendors')],
            'main_table.id = ca.c_quote_id',
            ['ca.vendor_status']
        )->where("ca.vendor_id=".$vendorId);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        return [
            'totalRecords' => count($this->collection->getData()),
            'items' => $this->collection->getData(),
        ];
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }


}