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

namespace Ced\CsMarketplace\Model\System\Config\Source\Vproducts;

/**
 * Class Set
 * @package Ced\CsMarketplace\Model\System\Config\Source\Vproducts
 */
class Set extends \Ced\CsMarketplace\Model\System\Config\Source\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManage;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModal;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $attrSetCollection;

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
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->marketplaceDataHelper = $marketplaceDataHelper;
        $this->storeManage = $storeManage;
        $this->productModal = $productModal;
        $this->attrSetCollection = $attrSetCollection;
    }

    /**
     * Retrieve Option values array
     *
     * @param bool $defaultValues
     * @param bool $withEmpty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray($defaultValues = false, $withEmpty = false)
    {
        $options = $allowedSet = [];
        $sets = $this->attrSetCollection
            ->setEntityTypeFilter($this->productModal->getResource()->getTypeId())->load()->toOptionHash();
        if (!$defaultValues) {
            $allowedSet = $this->getAllowedSet($this->storeManage->getStore(null)->getId());
        }

        foreach ($sets as $value => $label) {
            if (!$defaultValues && !in_array($value, $allowedSet)) continue;

            $options[] = ['value' => $value, 'label' => $label];
        }

        if ($withEmpty) {
            array_unshift($options, ['label' => '', 'value' => '']);
        }

        return $options;
    }

    /**
     * Get Allowed product attribute set
     * @param int $storeId
     * @return array
     */
    public function getAllowedSet($storeId = 0)
    {
        if ($storeId) {
            return explode(',',
                $this->marketplaceDataHelper->getStoreConfig('ced_csmarketplace/general/set', $storeId)
            );
        }

        return explode(',', $this->marketplaceDataHelper->getStoreConfig('ced_csmarketplace/general/set'));
    }
}
