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

namespace Ced\CsVendorProductAttribute\Model;

/**
 * Class Attributeset
 * @package Ced\CsVendorProductAttribute\Model
 */
class Attributeset extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceHelperData;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * Attributeset constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceHelperData
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Helper\Data $marketplaceHelperData,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->setFactory = $setFactory;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsVendorProductAttribute\Model\ResourceModel\Attributeset');
    }

    /**
     * @param $vendor
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getProductAttributeSets($vendor)
    {
        if (!is_numeric($vendor))
            $vendorId = $vendor->getId();
        else
            $vendorId = $vendor;

        $attributeSets = $this->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        return $attributeSets;
    }

    /**
     * @param null $vendor_id
     * @return array
     */
    public function getAllowedAttributeSets($vendor_id = null)
    {
        if ($vendor_id == null)
            $vendor_id = $this->customerSession->getVendorId();

        $vendor_attr_sets = [];
        if ($vendor_id) {
            $vendor_attrset = $this->getProductAttributeSets($vendor_id)->getData();

            foreach ($vendor_attrset as $key => $attrset_id) {
                $vendor_attr_sets[] = ['value' => $attrset_id['attribute_set_id'],
                    'label' => $attrset_id['attribute_set_code']];
            }
        }
        return $vendor_attr_sets;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedAttributeSetsByAdmin()
    {
        if ($this->scopeConfig == null)

            $allowedSet = [];
        $allowedSet = explode(',', $this->marketplaceHelperData->getStoreConfig('ced_csmarketplace/general/set',
            $this->storeManager->getStore()->getId())??'');

        $allowed_attr_sets = [];
        foreach ($allowedSet as $setId) {
            $set = $this->setFactory->create()->load($setId)->getData();

            if ($set != NULL) {
                $allowed_attr_sets[] = ['attribute_set_id' => $set['attribute_set_id'],
                    'attribute_set_code' => $set['attribute_set_name']];
            }

        }
        return $allowed_attr_sets;
    }
}
