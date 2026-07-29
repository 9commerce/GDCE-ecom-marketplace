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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsProduct\Ui\DataProvider\Product\Bundle;

use Ced\CsMarketplace\Model\Vproducts;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Bundle\Helper\Data;

class BundleDataProvider extends ProductDataProvider
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Vproducts
     */
    protected $_vproduct;

    /**
     * BundleDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Vproducts $vproduct
     * @param Data $dataHelper
     * @param array $meta
     * @param array $data
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Vproducts $vproduct,
        Data $dataHelper,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->dataHelper = $dataHelper;
        $this->_vproduct = $vproduct;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $vproducts = $this->_vproduct->getVendorProductIds();
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->addAttributeToFilter(
                'type_id',
                $this->dataHelper->getAllowedSelectionTypes()
            );
            $this->getCollection()->addFilterByRequiredOptions();
            $this->getCollection()->addStoreFilter(
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            );
            $this->getCollection()->addFieldToFilter('entity_id', ['in'=>$vproducts])->load();
        }

        $items = $this->getCollection()->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
