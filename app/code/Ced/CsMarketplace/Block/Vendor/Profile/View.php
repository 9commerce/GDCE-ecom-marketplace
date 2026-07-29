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

namespace Ced\CsMarketplace\Block\Vendor\Profile;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class View
 * @package Ced\CsMarketplace\Block\Vendor\Profile
 */
class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    public $_vendorFactory;

    /**
     * @var int
     */
    public $_totalattr;

    /**
     * @var int
     */
    public $_savedattr;

    /**
     * @var null
     */
    protected $_timeZone = null;

    /**
     * @var null
     */
    protected $_vendorAttribute = null;

    /**
     * @var \Magento\Directory\Model\Region
     */
    protected $_regionModel;

    /**
     * @var \Magento\Directory\Model\Country
     */
    protected $_countryModel;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Attribute
     */
    protected $_vattribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $_setCollection;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    protected $_groupCollection;

    /**
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * View constructor.
     * @param \Magento\Directory\Model\Region $regionModel
     * @param TimezoneInterface $timezone
     * @param \Magento\Directory\Model\Country $countryModel
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vattribute
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $groupCollection
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Directory\Model\Region $regionModel,
        TimezoneInterface $timezone,
        \Magento\Directory\Model\Country $countryModel,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vattribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $groupCollection,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $manager,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->_vendorFactory = $vendorFactory;
        $this->_regionModel = $regionModel;
        $this->timezone = $timezone;
        $this->_countryModel = $countryModel;
        $this->_vattribute = $vattribute;
        $this->_setCollection = $setCollection;
        $this->_groupCollection = $groupCollection;
        $this->sessionFactory = $sessionFactory;
        $this->manager = $manager;
        $this->_totalattr = 0;
        $this->_savedattr = 0;
    }

    /**
     * @param $country_id
     * @return mixed
     */
    public function getGroup($country_id)
    {
        return $country_id;
    }

    /**
     * @param $country_id
     * @return string
     */
    public function getCountryIdValue($country_id)
    {
        $regionCollection = $this->_regionModel->getCollection()->addCountryFilter($country_id);
        return ($regionCollection->getData() != null) ? 'true' : 'false';
    }

    /**
     * @param $region_id
     * @return string
     */
    public function getRegionFromId($region_id)
    {
        $regionName = "";
        foreach ($this->_regionModel->getCollection() as $region) {
            if ($region->getData('region_id') == $region_id) {
                $regionName = $region->getData('default_name');
                break;
            }
        }
        return $regionName;
    }

    /**
     * @param $date
     * @param string $format
     * @return mixed
     * @throws \Exception
     */
    public function getDate($date, $format = 'm/d/y')
    {
        $formatedDate = $this->timezone->date(new \DateTime($date))->format($format);
        return $formatedDate;
    }

    /**
     * @param $cid
     * @return string
     */
    public function getCountryId($cid)
    {
        $country = $this->_countryModel->loadByCode($cid);
        return $country->getName();
    }

    /**
     * @return mixed
     */
    public function getMediaUrl()
    {
        $_storeManager = $this->getStoreManager();
        $mediaUrl = $_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * @param $id
     * @param int $storeId
     * @return mixed
     */
    public function getVendorAttribute($id, $storeId = 0)
    {
        if ($this->_vendorAttribute == null) {
            $this->_vendorAttribute = $this->_vattribute->create();
        }

        return $this->_vendorAttribute->setStoreId($storeId)->load($id);
    }

    /**
     * Preparing collection of vendor attribute group vise
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorAttributeInfo()
    {
        $vendor = $this->getVendor();
        $entityTypeId = $vendor->getEntityTypeId();

        $setIds = $this->_setCollection->setEntityTypeFilter($entityTypeId)->getAllIds();

        $groupCollection = $this->_groupCollection;
        if (count($setIds) > 0) {
            $groupCollection->addFieldToFilter('attribute_set_id', array('in' => $setIds));
        }

        $groupCollection->setSortOrder()->load();
        $total = 0;
        $saved = 0;

        foreach ($groupCollection as $group) {
            $attributes = $vendor->getAttributes($group->getId(), true);
            if (count($attributes) == 0) {
                continue;
            }
        }
        $this->_totalattr = $total;
        $this->_savedattr = $saved;
        return $groupCollection;
    }

    /**
     * @return \Ced\CsMarketplace\Model\Vendor
     */
    public function getVendor()
    {
        return $this->_vendorFactory->create()->load($this->getVendorId());
    }

    /**
     * @return int
     */
    public function getVendorId()
    {
        return $this->sessionFactory->create()->getVendorId();
    }

    /**
     * @param $module
     * @return bool
     */
    public function isModuleEnable($module)
    {
        return $this->manager->isEnabled($module);
    }
}
