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
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Model\System\Config\Source\Vproducts;

/**
 * Class Set
 * @package Ced\CsVendorProductAttribute\Model\System\Config\Source\Vproducts
 */
class Set extends \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $attrSetCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManage;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModal;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\Attributeset
     */
    protected $attributeset;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceDataHelper;

    /**
     * Set constructor.
     * @param \Ced\CsVendorProductAttribute\Model\Attributeset $attributeset
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManage
     * @param \Magento\Catalog\Model\Product $productModal
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Ced\CsVendorProductAttribute\Model\Attributeset $attributeset,
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManage,
        \Magento\Catalog\Model\Product $productModal,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    )
    {
        parent::__construct(
            $marketplaceDataHelper,
            $storeManage,
            $productModal,
            $attrSetCollection,
            $attrOptionCollectionFactory,
            $attrOptionFactory
        );
        $this->attrSetCollection = $attrSetCollection;
        $this->storeManage = $storeManage;
        $this->productModal = $productModal;
        $this->attributeset = $attributeset;
        $this->marketplaceDataHelper = $marketplaceDataHelper;
    }

    /**
     * @param $defaultValues
     * @param $withEmpty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray($defaultValues = false, $withEmpty = false)
    {
        $sets = $this->attrSetCollection
            ->setEntityTypeFilter($this->productModal->getResource()->getTypeId())
            ->load()
            ->toOptionHash();
        if (!$defaultValues)
            $allowedSet = $this->getAllowedSet($this->storeManage->getStore(null)->getId());

        $vendorSets = $this->getVendorCreatedSets();
        $options = [];
        foreach ($sets as $value => $label) {
            if ((!$defaultValues && !in_array($value, $allowedSet)) || in_array($value, $vendorSets)) continue;
            $options[] = ['value' => $value, 'label' => $label];
        }
        if ($withEmpty) {
            array_unshift($options, ['label' => '', 'value' => '']);
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getVendorCreatedSets()
    {
        $data = [];
        $set = $this->attributeset->getCollection()->addFieldToFilter('vendor_id', ['neq' => 0])->getData();
        foreach ($set as $value) {
            $data[] = $value['attribute_set_id'];
        }
        return $data;
    }

    /**
     * Get Allowed product attribute set
     *
     */
    public function getAllowedSet($storeId = 0)
    {
        $vendorSets = $this->getVendorCreatedSets();
        if ($storeId) {
            $allowed_attr_sets = explode(',',
                $this->marketplaceDataHelper->getStoreConfig('ced_csmarketplace/general/set', $storeId)??'');
            return array_merge($allowed_attr_sets, $vendorSets);
        }
        $allowed_attr_sets = explode(',',
            $this->marketplaceDataHelper->getStoreConfig('ced_csmarketplace/general/set')??'');
        return array_merge($allowed_attr_sets, $vendorSets);
    }
}
