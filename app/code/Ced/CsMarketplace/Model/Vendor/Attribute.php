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

namespace Ced\CsMarketplace\Model\Vendor;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;


/**
 * Class Attribute
 * @package Ced\CsMarketplace\Model\Vendor
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute
{

    /**
     * Prefix of vendor attribute events names
     *
     * @var string
     */
    protected $_eventPrefix = 'csmarektplace_vendor_attribute';

    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    /**
     * @var
     */
    protected $_setup;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $_vendor;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_eavCollectionFactory;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Form
     */
    protected $_vendorForm;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    protected $_attributeGroupCollection;

    /**
     * @var \Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory
     */
    protected $_csMarketplaceSetup;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param Form $vendorForm
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory $csMarketplaceSetup
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Model\Vendor\Form $vendorForm,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $attributeGroupCollection,
        \Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory $csMarketplaceSetup,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            null,
            null,
            $data
        );

        $this->_attributeGroupCollection = $attributeGroupCollection;
        $this->_csMarketplaceSetup = $csMarketplaceSetup;
        $this->_vendor = $vendor;
        $this->_eavCollectionFactory = $eavCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_vendorForm = $vendorForm;
        $this->_resourceConnection = $resourceConnection;

        $this->setEntityTypeId($this->_vendor->getEntityTypeId());
        $setIds = $this->_eavCollectionFactory->create()->setEntityTypeFilter($this->getEntityTypeId())->getAllIds();
        $this->setAttributeSetIds($setIds);

    }


    /**
     * Set store scope
     * @param $store
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStore($store)
    {
        $this->setStoreId($this->_storeManager->getStore($store)->getId());
        return $this;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID;
    }

    /**
     * Load vendor's attributes into the object
     * @param int $entityId
     * @param null $field
     * @return $this
     */
    public function load($entityId, $field = null)
    {
        parent::load($entityId, $field);
        if ($this && $this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $this->setData(
                        'is_visible',
                        $joinField->getIsVisible()
                    );
                    $this->setData(
                        'position',
                        $joinField->getSortOrder()
                    );
                    $this->setData(
                        'use_in_registration',
                        $joinField->getData('use_in_registration')
                    );
                    $this->setData(
                        'position_in_registration',
                        $joinField->getData('position_in_registration')
                    );
                    $this->setData(
                        'use_in_left_profile',
                        $joinField->getData('use_in_left_profile')
                    );
                    $this->setData(
                        'fontawesome_class_for_left_profile',
                        $joinField->getData('fontawesome_class_for_left_profile')
                    );
                    $this->setData(
                        'position_in_left_profile',
                        $joinField->getData('position_in_left_profile')
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @param $attribute
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _vendorForm($attribute)
    {
        $store = $this->getStoreId();
        $fields = $this->_vendorForm->getCollection()
            ->addFieldToFilter('attribute_id', array('eq' => $attribute->getAttributeId()))
            ->addFieldToFilter('attribute_code', array('eq' => $attribute->getAttributeCode()))
            ->addFieldToFilter('store_id', array('eq' => $store));
        if (count($fields) == 0) {
            $data[] = array(
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'is_visible' => 0,
                'sort_order' => 0,
                'store_id' => $store,
                'use_in_registration' => 0,
                'position_in_registration' => 0,
                'use_in_left_profile' => 0,
                'fontawesome_class_for_left_profile' => 'fa fa-circle-thin',
                'position_in_left_profile' => 0,
            );
            $this->_vendorForm->insertMultiple($data);
            return $this->_vendorForm($attribute);
        }
        return $fields;
    }

    /**
     * Return current store id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->setStoreId($this->_storeManager->getStore(null)->getId());
        }
        return $this->_storeId;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\StoreManagerInterface) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Retrive Vendor attribute collection
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = parent::getCollection();
        $typeId = $this->_vendor->getEntityTypeId();
        $collection = $collection->addFieldToFilter('entity_type_id', array('eq' => $typeId));
        $labelTableName = $this->_resourceConnection->getTableName('eav_attribute_label');

        $tableName = $this->_resourceConnection->getTableName('ced_csmarketplace_vendor_form_attribute');
        if ($this->getStoreId()) {
            $availableStoreWiseIds = $this->getStoreWiseIds($this->getStoreId());
            $collection
                ->getSelect()
                ->join(array('vform' => $tableName),
                    'main_table.attribute_id=vform.attribute_id',
                    array('is_visible' => 'vform.is_visible',
                        'sort_order' => 'vform.sort_order',
                        'store_id' => 'vform.store_id',
                        'use_in_registration' => 'vform.use_in_registration',
                        'use_in_left_profile' => 'vform.use_in_left_profile',
                        'position_in_registration' => 'vform.position_in_registration',
                        'position_in_left_profile' => 'vform.position_in_left_profile',
                        'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile'
                    )
                );
            $collection->getSelect()
                ->where('(vform.attribute_id IN ("' . $availableStoreWiseIds . '") AND 
                                vform.store_id=' . $this->getStoreId() . ') OR 
                                (vform.attribute_id NOT IN ("' . $availableStoreWiseIds . '") AND vform.store_id=0)');
            $collection->getSelect()
                ->group('vform.attribute_id');
            $collection->getSelect()
                ->joinLeft(array('vlabel' => $labelTableName),
                    'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=' . $this->getStoreId(),
                    array('store_label' => 'vlabel.value'));
        } else {
            $collection->getSelect()
                ->join(array('vform' => $tableName),
                    'main_table.attribute_id=vform.attribute_id && vform.store_id=0',
                    array('is_visible' => 'vform.is_visible',
                        'sort_order' => 'vform.sort_order',
                        'store_id' => 'vform.store_id',
                        'use_in_registration' => 'vform.use_in_registration',
                        'use_in_left_profile' => 'vform.use_in_left_profile',
                        'position_in_registration' => 'vform.position_in_registration',
                        'position_in_left_profile' => 'vform.position_in_left_profile',
                        'fontawesome_class_for_left_profile' => 'vform.fontawesome_class_for_left_profile'));
            $collection->getSelect()
                ->joinLeft(array('vlabel' => $labelTableName),
                    'main_table.attribute_id=vlabel.attribute_id && vlabel.store_id=0',
                    array('store_label' => 'vlabel.value'));
        }
        return $collection;
    }

    /**
     * @param int $storeId
     * @return array|string
     */
    public function getStoreWiseIds($storeId = 0)
    {
        if ($storeId) {
            $allowed = [];
            foreach ($this->_vendorForm->getCollection()
                         ->addFieldToFilter('store_id', ['eq' => $storeId])
                     as $attribute) {
                $allowed[] = $attribute->getAttributeId();
            }
            return implode(',', $allowed);
        }
        return [];
    }


    /**
     * @param array $group
     */
    public function addToGroup($group = [])
    {
        if (count($group) > 0) {
            $setIds = $this->_eavCollectionFactory->create()
                ->setEntityTypeFilter($this->getEntityTypeId())->getAllIds();
            $setId = isset($setIds[0]) ? $setIds[0] : $this->getEntityTypeId();
            $installer = $this->_csMarketplaceSetup->create();

            if (!in_array($group, $this->getGroupOptions($setId, true))) {
                $installer->addAttributeGroup(
                    'csmarketplace_vendor',
                    $setId,
                    $group
                );
            }
            $installer->addAttributeToGroup(
                'csmarketplace_vendor',
                $setId,
                $group, //Group Name
                $this->getAttributeId()
            );
        }
    }

    /**
     * @param $setId
     * @param bool $flag
     * @return array
     */
    protected function getGroupOptions($setId, $flag = false)
    {
        $groupCollection = $this->_attributeGroupCollection->setAttributeSetFilter($setId);

        $groupCollection->setSortOrder()->load();
        $options = [];
        if ($flag) {
            foreach ($groupCollection as $group) {
                $options[] = $group->getAttributeGroupName();
            }
        } else {
            foreach ($groupCollection as $group) {
                $options[$group->getId()] = $group->getAttributeGroupName();
            }
        }
        return $options;
    }

    /**fontawesome_class_for_left_profile
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $joinField->delete();
                }
            }
        }
        return parent::delete();
    }


    /**
     * Processing vendor attribute after save data
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSave()
    {
        parent::afterSave();
        if ($this->getId()) {
            $joinFields = $this->_vendorForm($this);
            if (count($joinFields) > 0) {
                foreach ($joinFields as $joinField) {
                    $joinField->setData(
                        'is_visible',
                        $this->getData('is_visible')
                    );
                    $joinField->setData(
                        'sort_order',
                        $this->getData('position')
                    );
                    $joinField->setData(
                        'use_in_registration',
                        $this->getData('use_in_registration')
                    );
                    $joinField->setData(
                        'position_in_registration',
                        $this->getData('position_in_registration')
                    );
                    $joinField->setData(
                        'use_in_left_profile',
                        $this->getData('use_in_left_profile')
                    );
                    $joinField->setData(
                        'fontawesome_class_for_left_profile',
                        $this->getData('fontawesome_class_for_left_profile')
                    );
                    $joinField->setData(
                        'position_in_left_profile',
                        $this->getData('position_in_left_profile')
                    );
                    $joinField->save();
                }
            }
        }
        return $this;
    }
}
