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

namespace Ced\CsProduct\Model\System\Config\Source\Vproducts;

class Set extends \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $attrSetCollection;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModal;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManage;

    /**
     * Set constructor.
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManage
     * @param \Magento\Catalog\Model\Product $productModal
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManage,
        \Magento\Catalog\Model\Product $productModal,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        $this->attrSetCollection = $attrSetCollection;
        $this->productModal = $productModal;
        $this->storeManage = $storeManage;
        parent::__construct(
            $marketplaceDataHelper,
            $storeManage,
            $productModal,
            $attrSetCollection,
            $attrOptionCollectionFactory,
            $attrOptionFactory
        );
    }

    /**
     * @param bool $defaultValues
     * @param bool $withEmpty
     * @return array
     */
    public function toOptionArray($defaultValues = false, $withEmpty = false)
    {
        $options = [];

        $sets = $this->attrSetCollection
            ->setEntityTypeFilter($this->productModal->getResource()->getTypeId())
            ->load()
            ->toOptionHash();
        if (!$defaultValues) {
            $allowedSet = $this->getAllowedSet($this->storeManage->getStore(null)->getId());
        }

        $vendorSets = $this->getVendorCreatedSets();
        foreach ($sets as $value => $label) {
            if ((!$defaultValues && !in_array($value, $allowedSet)) || in_array($value, $vendorSets)) {
                continue;
            }
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
        $ob = \Magento\Framework\App\ObjectManager::getInstance();
        $data = [];
        $set = $ob->create(\Ced\CsVendorProductAttribute\Model\Attributeset::class)->getCollection()->getData();
        foreach ($set as $value) {
            $data[] = $value['attribute_set_id'];
        }
        return $data;
    }
}
