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
 * Class Attribute
 * @package Ced\CsVendorProductAttribute\Model
 */
class Attribute extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    protected $attributeManagement;

    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Catalog\Model\Config $config,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->config = $config;
        $this->attributeManagement = $attributeManagement;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsVendorProductAttribute\Model\ResourceModel\Attribute');
    }

    /**
     * @param $vendor
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getProductAttributes($vendor)
    {
        if (!is_numeric($vendor))
            $vendorId = $vendor->getId();
        else
            $vendorId = $vendor;

        $attributes = $this->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        return $attributes;
    }

    /**
     * @param array $attributeSetIds
     * @param $attribute_id
     * @param $attributeCode
     * @param int $sortOrder
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addVendorAttributeToAttributeSet($attributeSetIds, $attribute_id, $attributeCode, $sortOrder = 150)
    {
        foreach ($attributeSetIds as $attributeSetId) {

            $attributeGroupId = $this->config->getAttributeGroupId($attributeSetId, 'General');
            if (!$attributeGroupId) {
                $attributeGroupId = $this->config->getAttributeGroupId($attributeSetId, 'Product Details');
            }
            if ($attributeSetId != null) {
                $this->attributeManagement
                    ->assign('catalog_product', $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
            }
        }
    }

    /**
     * @param array $attributeSetIds
     * @param $attribute_id
     * @param $attributeCode
     * @param int $sortOrder
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addVendorAttributeToGroup($attributeSetIds, $attribute_id, $attributeCode, $sortOrder = 150)
    {
        foreach ($attributeSetIds as $attributeSetId) {

            $attributeGroupId = $this->config->getAttributeGroupId($attributeSetId, 'General');
            if (!$attributeGroupId) {
                $attributeGroupId = $this->config->getAttributeGroupId($attributeSetId, 'Product Details');
            }
            if ($attributeSetId != null) {
                $this->attributeManagement
                    ->assign('catalog_product', $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
            }
        }
    }

    /**
     * @param array $attributeSetIds
     * @param $attribute_id
     * @param $attributeCode
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function removeVendorAttributeFromGroup($attributeSetIds, $attribute_id, $attributeCode)
    {
        foreach ($attributeSetIds as $attributeSetId) {

            if ($attributeSetId != null) {
                $this->attributeManagement->unassign($attributeSetId, $attributeCode);
            }
        }

    }
}
