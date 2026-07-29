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

namespace Ced\CsMarketplace\Block\Vshops;

use Ced\CsMarketplace\Block\Vshops\Catalog\Product\ListProduct;
use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Tool\Image;
use Ced\CsMarketplace\Model\Vendor\AttributeFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class View
 * @package Ced\CsMarketplace\Block\Vshops
 */
class View extends Template
{

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var TimezoneInterface
     */
    public $_timezone;

    /**
     * @var AttributeFactory
     */
    public $_attribute;

    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Acl
     */
    protected $aclHelper;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * View constructor.
     * @param File $file
     * @param Acl $aclHelper
     * @param Image $imageHelper
     * @param Registry $registry
     * @param TimezoneInterface $timezone
     * @param AttributeFactory $attribute
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        File $file,
        Acl $aclHelper,
        Image $imageHelper,
        Registry $registry,
        TimezoneInterface $timezone,
        AttributeFactory $attribute,
        Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->aclHelper = $aclHelper;
        $this->imageHelper = $imageHelper;
        $this->file = $file;
        $this->_attribute = $attribute;
        $this->_timezone = $timezone;
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $registry;
        $this->_countryFactory = $countryFactory;
        if ($this->getVendor()) {
            $vendor = $this->getVendor();
            if ($vendor->getMetaDescription())
                $this->pageConfig->setDescription($vendor->getMetaDescription());
            if ($vendor->getMetaKeywords())
                $this->pageConfig->setKeywords($vendor->getMetaKeywords());
        }

    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
    public function getTimezone($date)
    {
        return $this->_timezone->date(new \DateTime($date))->format('m/d/y');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor)
            $this->_vendor = $this->_coreRegistry->registry('current_vendor');
        return $this->_vendor;
    }

    /**
     * @param $key
     */
    public function camelize($key)
    {
        return $this->_camelize($key);
    }

    /**
     * @param $name
     */
    protected function _camelize($name)
    {
        $this->uc_words($name, '');
    }

    /**
     * @param $str
     * @param string $destSep
     * @param string $srcSep
     * @return mixed
     */
    public function uc_words($str, $destSep = '_', $srcSep = '_')
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }

    /**
     * @param null $storeId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function getLeftProfileAttributes($storeId = null)
    {
        if ($storeId == null) $storeId = $this->_storeManager->getStore()->getId();
        $attributes = $this->_attribute->create()->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('use_in_left_profile', ['gt' => 0])
            ->setOrder('position_in_left_profile', 'ASC');
        $this->_eventManager->dispatch(
            'ced_csmarketplace_left_profile_attributes_load_after',
            ['attributes' => $attributes]
        );
        return $attributes;
    }


    /**
     * @return mixed
     */
    public function getVendorLogo()
    {
        return $this->getVendor()->getData('profile_picture');
    }

    /**
     * @return mixed
     */
    public function getVendorBanner()
    {
        return $this->getVendor()->getData('company_banner');
    }

    /**
     * @param $code
     * @return bool
     */

    public function Method($code)
    {
        if ($this->getVendor()->getData($code) != "") {
            return $this->getVendor()->getData($code);
        }
        return false;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function baseName($path)
    {
        $fileInfo = $this->file->getPathInfo($path);
        return $fileInfo['basename'];
    }

    /**
     * @return Acl
     */
    public function aclHelper()
    {
        return $this->aclHelper;
    }

    /**
     * @return Image
     */
    public function imageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * @return mixed
     */
    public function search()
    {
        return $this->getRequest()->getParam(ListProduct::SEARCH_QUERY_PARAM, '');
    }

    public function getCountryname($countryCode){    
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

}
