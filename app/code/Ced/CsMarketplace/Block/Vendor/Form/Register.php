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

namespace Ced\CsMarketplace\Block\Vendor\Form;


use Magento\Customer\Model\AccountManagement;

/**
 * Class Register
 * @package Ced\CsMarketplace\Block\Vendor\Form
 */
class Register extends \Magento\Directory\Block\Data
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_vendorUrl;

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $_storeInfo;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * Register constructor. Story
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMarketplace\Model\Url $vendorUrl
     * @param \Magento\Store\Model\Information $storeInfo
     * @param \Magento\Store\Model\Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMarketplace\Model\Url $vendorUrl,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Store\Model\Store $store,
        \Ced\CsMarketplace\Helper\Data $helper,
        array $data = []
    ) {

        $this->_vendorUrl = $vendorUrl;
        $this->_moduleManager = $moduleManager;
        $this->_customerSession = $customerSession;
        $this->_storeInfo = $storeInfo;
        $this->_store = $store;
        $this->_helper = $helper;

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_vendorUrl->getRegisterPostUrl();
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if ($url === null) {
            $url = $this->_vendorUrl->getLoginUrl();
        }
        return $url;
    }

    /**
     * Retrieve customer country identifier
     *
     * @return int
     */
    public function getCountryId()
    {
        $postCountryId = $this->getFormData()->getCountryId();
        if ($postCountryId) {
            return $postCountryId;
        }
        return parent::getCountryId();
    }

    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData()
    {
        $formData = $this->getData('form_data');
        if ($formData === null) {
            $cedFormData = $this->_customerSession->getCustomerFormData(true);
            $formData = new \Magento\Framework\DataObject();
            if ($cedFormData) {
                $formData->addData($cedFormData);
                $formData->setCustomerData(1);
            }
            if (isset($formData['region_id'])) {
                $formData['region_id'] = (int)$formData['region_id'];
            }
            $this->setData('form_data', $formData);
        }
        return $formData;
    }

    /**
     * Retrieve customer region identifier
     *
     * @return mixed
     */
    public function getRegion()
    {
        if (null !== ($postRegion = $this->getFormData()->getRegion())) {
            return $postRegion;
        } elseif (null !== ($postRegion = $this->getFormData()->getRegionId())) {
            return $postRegion;
        }
        return null;
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        $isNewsletterEnabled = $this->_moduleManager->isOutputEnabled('Magento_Newsletter');
        return $isNewsletterEnabled;
    }

    /**
     * Restore entity data from session
     * Entity and form code must be defined for the form
     *
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @param string|null $scope
     * @return $this
     */
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $form, $scope = null)
    {
        if ($this->getFormData()->getCustomerData()) {
            $request = $form->prepareRequest($this->getFormData()->getData());
            $formData = $form->extractData($request, $scope, false);
            $form->restoreData($formData);
        }
        return $this;
    }

    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Get number of password required character classes
     *
     * @return string
     * @since 100.1.0
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_vendorUrl->getLoginUrl();
    }

    /**
     * @return mixed
     */
    public function getStorePhoneNumber()
    {
        return $this->_storeInfo->getStoreInformationObject($this->_store)->getPhone();
    }

    public function isFacebookLinkEnabled()
    {
        if ($this->_helper->getStoreConfig('ced_csmarketplace/social_links/enable_facebook_link',
            $this->_store->getStoreId())
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getFacebookId()
    {
        return $this->_helper->getStoreConfig('ced_csmarketplace/social_links/facebook_id',
            $this->_store->getStoreId());
    }

    /**
     * @return bool
     */
    public function isTwitterLinkEnabled()
    {
        if ($this->_helper->getStoreConfig('ced_csmarketplace/social_links/enable_twitter_link',
            $this->_store->getStoreId())
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getTwitterId()
    {
        return $this->_helper->getStoreConfig(
            'ced_csmarketplace/social_links/twitter_id',
            $this->_store->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isLinkedinLinkEnabled()
    {
        if ($this->_helper->getStoreConfig('ced_csmarketplace/social_links/enable_linkedin_link',
            $this->_store->getStoreId())
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getLinkedinId()
    {
        return $this->_helper->getStoreConfig(
            'ced_csmarketplace/social_links/linkedin_id',
            $this->_store->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function isInstagramLinkEnabled()
    {
        if ($this->_helper->getStoreConfig('ced_csmarketplace/social_links/enable_instagram_link',
            $this->_store->getStoreId())
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getInstagramId()
    {
        return $this->_helper->getStoreConfig(
            'ced_csmarketplace/social_links/instagram_id',
            $this->_store->getStoreId()
        );
    }


    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Create New Vendor Account'));
        return parent::_prepareLayout();
    }
}
