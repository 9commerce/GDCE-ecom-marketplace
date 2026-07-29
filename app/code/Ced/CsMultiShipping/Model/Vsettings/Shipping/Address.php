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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Model\Vsettings\Shipping;

use Magento\Framework\Api\AttributeValueFactory;

class Address extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'address';

    /**
     * @var array
     */
    protected $_fields = [];

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Country
     */
    protected $country;

    /**
     * Address constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Config\Model\Config\Source\Locale\Country $country
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get current store
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get current store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId) {
            return $this->csmarketplaceHelper->getStore($storeId);
        } else {
            return $this->csmarketplaceHelper->getStore();
        }
    }

    /**
     * Get the code
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     *  Retreive input fields
     * @return array
     */
    public function getFields()
    {
        $this->_fields = [];
        $this->_fields['country_id'] = [
            'type' => 'select',
            'required' => true,
            'values' => $this->getCoutryOptionArray()
        ];
        $this->_fields['region_id'] = [
            'type' => 'select',
            'required' => true,
            'values' => [
                ['label' => __('Please select region, state or province'), 'value' => '']
            ]
        ];
        $this->_fields['region'] = ['type' => 'text', 'required' => true];
        $this->_fields['city'] = ['type' => 'text', 'required' => true];
        $this->_fields['postcode'] = ['type' => 'text', 'required' => true];
        $this->_fields['postcode']['after_element_html'] = "";
        if($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/csmultishipping/min_order',$this->csmarketplaceHelper->getStore()->getId()))
        $this->_fields['min_order'] = ['type' => 'text', 'required' => false, 'class'=>'validate-not-negative-number'];
        return $this->_fields;
    }

    protected function getCoutryOptionArray()
    {
        return $this->getCountryCollection()
            ->setForegroundCountries([])
            ->toOptionArray();
    }

    /**
     * Returns country collection instance
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->_countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get the code separator
     * @return string
     */
    public function getCodeSeparator()
    {
        return $this->_codeSeparator;
    }

    /**
     * Retreive labels
     *
     * @param string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label':
                return __('Origin Address Details');
            case 'country_id':
                return __('Country');
            case 'region':
            case 'region_id':
                return __('State/Province');
            case 'city':
                return __('City');
            case 'postcode':
                return __('Zip/Postal Code');
            case 'min_order':
                return __('Min Order Amount');
            default:
                return __(ucfirst($key));
        }
    }
}
