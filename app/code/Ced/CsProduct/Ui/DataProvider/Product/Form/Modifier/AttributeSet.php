<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * s://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Field;

class AttributeSet extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet
{
    /**
     * Sort order of "Attribute Set" field inside of fieldset
     */
    const ATTRIBUTE_SET_FIELD_ORDER = 30;

    /**
     * Set collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * AttributeSet constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Framework\Registry $registry
     * @param LocatorInterface $locator
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $manager,
        \Magento\Framework\Registry $registry,
        LocatorInterface $locator,
        CollectionFactory $attributeSetCollectionFactory,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($locator, $attributeSetCollectionFactory, $urlBuilder);
        $this->locator = $locator;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->manager = $manager;
        $this->registry = $registry;
    }

    /**
     * Return options for select
     *
     * @return array
     */
    public function getOptions()
    {
        $attributeSetIds = [];
        $is_vpattribute = $this->manager->isEnabled('Ced_CsVendorProductAttribute');
        $storeId = $this->registry->registry('current_store_data')->getId();

        if ($is_vpattribute && $this->scopeConfig->getValue(
            'ced_csmarketplace/general/vpattributes_activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        )) {
            $vendor = $this->registry->registry('vendor');
            $vendorId = $vendor['entity_id'];

            $ob = \Magento\Framework\App\ObjectManager::getInstance();
            $attributesetvalues = $ob->create(\Ced\CsVendorProductAttribute\Model\Attributeset::class)
                ->getCollection()->addFieldtoFilter('vendor_id', $vendorId)->getData();

            foreach ($attributesetvalues as $value) {
                $attributeSetIds[] = $value['attribute_set_id'];
            }
        }
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->setEntityTypeFilter($this->locator->getProduct()->getResource()->getTypeId())
            ->addFieldToSelect('attribute_set_id', 'value')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder(
                'attribute_set_name',
                \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
            );
        $attributesetIds = explode(',', $this->scopeConfig->getValue(
            'ced_csmarketplace/general/set',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        $attributesetids = array_merge($attributeSetIds, $attributesetIds);
        $collection->addFieldToFilter('attribute_set_id', ['in' => $attributesetids]);
        return $collection->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($name = $this->getGeneralPanelName($meta)) {
            $meta[$name]['children']['attribute_set_id']['arguments']['data']['config'] = [
                'component' => 'Magento_Catalog/js/components/attribute-set-select',
                'disableLabel' => true,
                'filterOptions' => true,
                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                'formElement' => 'select',
                'componentType' => Field::NAME,
                'options' => $this->getOptions(),
                'visible' => 1,
                'required' => 1,
                'label' => __('Attribute Set'),
                'source' => $name,
                'dataScope' => 'attribute_set_id',
                'filterUrl' => $this->urlBuilder->getUrl(
                    'csproduct/vproducts/suggestAttributeSets',
                    ['isAjax' => 'true']
                ),
                'sortOrder' => $this->getNextAttributeSortOrder(
                    $meta,
                    [ProductAttributeInterface::CODE_STATUS],
                    self::ATTRIBUTE_SET_FIELD_ORDER
                ),
                'multiple' => false,
            ];
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return array_replace_recursive($data, [
            $this->locator->getProduct()->getId() => [
                self::DATA_SOURCE_DEFAULT => [
                    'attribute_set_id' => $this->locator->getProduct()->getAttributeSetId()
                ],
            ]
        ]);
    }
}
