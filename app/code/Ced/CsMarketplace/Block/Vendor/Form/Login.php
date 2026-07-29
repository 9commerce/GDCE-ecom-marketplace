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


/**
 * Class Login
 * @package Ced\CsMarketplace\Block\Vendor\Form
 */
class Login extends \Ced\CsMarketplace\Block\Vendor\Header
{

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Ced\CsMarketplace\Model\Url
     */
    protected $_vendorUrl;

    /**
     * @var \Ced\CsMarketplace\Helper\Cookie
     */
    protected $_cookieData;

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $_storeInfo;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    public $_helper;

    /**
     * @var \Ced\CsMarketplace\Helper\Tool\Image
     */
    public $imageHelper;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @var int
     */
    private $_username = -1;

    /**
     * Login constructor.
     * @param \Ced\CsMarketplace\Helper\Tool\Image $imageHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMarketplace\Model\Url $vendorUrl
     * @param \Ced\CsMarketplace\Helper\Cookie $cookieData
     * @param \Magento\Store\Model\Information $storeInfo
     * @param \Magento\Store\Model\Store $store
     * @param \Ced\CsMarketplace\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Tool\Image $imageHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMarketplace\Model\Url $vendorUrl,
        \Ced\CsMarketplace\Helper\Cookie $cookieData,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Store\Model\Store $store,
        \Ced\CsMarketplace\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($helper, $imageHelper, $context, $data);
        $this->_isScopePrivate = true;
        $this->_vendorUrl = $vendorUrl;
        $this->_customerSession = $customerSession;
        $this->_cookieData = $cookieData;
        $this->_storeInfo = $storeInfo;
        $this->_helper = $helper;
        $this->_store = $store;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_vendorUrl->getLoginPostUrl();
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->_vendorUrl->getForgotPasswordUrl();
    }

    /**
     * Retrieve customer register form url
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->_vendorUrl->getRegisterUrl();
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUsername()
    {
        $username = $this->_cookieData->getCookieEmail();
        if ($username != '') {
            $this->_username = $username;
        }
        if (-1 === $this->_username) {
            $this->_username = $this->_customerSession->getUsername(true);
        }
        return $this->_username;
    }

    /**
     * @return bool|string
     */
    public function getPassword()
    {
        $password = $this->_cookieData->getCookieUserPassword();
        if ($password != '') {
            return $password;
        }
        return '';
    }

    /**
     * @return int|string
     */
    public function getRememberMe()
    {
        $rememberme = $this->_cookieData->getCookieLoginCheck();
        if ($rememberme != '') {
            return $rememberme;
        }
        return '';
    }


    /**
     * @return bool
     */
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
    public function getStorePhoneNumber()
    {
        return $this->_storeInfo->getStoreInformationObject($this->_store)->getPhone();
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
    public function getFacebookId()
    {
        return $this->_helper->getStoreConfig('ced_csmarketplace/social_links/facebook_id',
            $this->_store->getStoreId());
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
    public function getTwitterId()
    {
        return $this->_helper->getStoreConfig(
            'ced_csmarketplace/social_links/twitter_id',
            $this->_store->getStoreId()
        );
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
        $this->pageConfig->getTitle()->set(__('Vendor Login'));
        return parent::_prepareLayout();
    }
}
