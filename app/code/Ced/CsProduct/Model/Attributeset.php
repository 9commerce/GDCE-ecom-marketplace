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

namespace Ced\CsProduct\Model;

class Attributeset extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set
     */
    protected $vproductsSet;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * Attributeset constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $vproductsSet
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set $vproductsSet,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->_storeManager = $_storeManager;
        $this->vproductsSet = $vproductsSet;
        $this->attributeSetFactory = $attributeSetFactory;
        parent::__construct($context, $registry);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedAttributeSets()
    {
        if ($this->_scopeConfig == null) {
            $allowedSet = $this->vproductsSet->getAllowedSet($this->_storeManager->getStore()->getId());
        }
        $allowed_attr_sets = [];
        foreach ($allowedSet as $setId) {
            $set = $this->attributeSetFactory->create()->load($setId);
            if ($set && $set->getId()) {
                $set->getData();
                $allowed_attr_sets[] = ['value' => $set['attribute_set_id'], 'label' => $set['attribute_set_name']];
            }
        }
        return $allowed_attr_sets;
    }
}
