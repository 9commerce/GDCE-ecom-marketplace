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

namespace Ced\CsVendorProductAttribute\Model\ResourceModel\Entity\Attribute\Grid;

/**
 * Class Collection
 * @package Ced\CsVendorProductAttribute\Model\ResourceModel\Entity\Attribute\Grid
 */
class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Grid\Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\AttributesetFactory
     */
    protected $attributesetFactory;

    /**
     * Collection constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsVendorProductAttribute\Model\AttributesetFactory $attributesetFactory
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registryManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsVendorProductAttribute\Model\AttributesetFactory $attributesetFactory,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registryManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->registryManager = $registryManager;
        $this->customerSession = $customerSession;
        $this->attributesetFactory = $attributesetFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $registryManager,
            $connection,
            $resource
        );
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setEntityTypeFilter($this->registryManager->registry('entityType'));
        $vendor_attr_sets = $this->getVendorAttrSets();
        $this->addFieldToFilter('attribute_set_id', ['in' => $vendor_attr_sets]);

        return $this;
    }

    /**
     * @return array
     */
    public function getVendorAttrSets()
    {
        $vendor_attr_sets = [];
        $vendor_id = $this->customerSession->getVendorId();
        if ($vendor_id) {
            $attrset_model = $this->attributesetFactory->create()->getCollection()
                ->addFieldToFilter('vendor_id', $vendor_id)
                ->addFieldToSelect('attribute_set_id')->getData();

            foreach ($attrset_model as $key => $attrset_id) {
                $vendor_attr_sets[] = $attrset_id['attribute_set_id'];
            }
        }
        return $vendor_attr_sets;
    }
}
