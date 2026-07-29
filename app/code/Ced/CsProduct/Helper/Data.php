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

namespace Ced\CsProduct\Helper;

use Magento\Customer\Model\Session;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $customerSession,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->_scopeConfigManager = $context->getScopeConfig();
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->_customerSession = $customerSession;
        $this->request = $http;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isVendorLoggedIn()
    {
        $vendorId = $this->_customerSession->getVendorId();
        if (!$vendorId) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getSimpleUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'simple'
            ]
        );
    }

    /**
     * @return string
     */
    public function getConfigurableUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'configurable'
            ]
        );
    }

    /**
     * @return string
     */
    public function getBundleUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'bundle'
            ]
        );
    }

    /**
     * @return string
     */
    public function getVirtualUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'virtual'
            ]
        );
    }

    /**
     * @return string
     */
    public function getDownloadableUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'downloadable'
            ]
        );
    }

    /**
     * @return string
     */
    public function getGroupedUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/new',
            [
                'set' => $this->getFirstAttributeSet(),
                'type' => 'grouped'
            ]
        );
    }

    /**
     * @return string
     */
    public function getWizardUrl()
    {
        return $this->urlBuilder->getUrl(
            'csproduct/vproducts/wizard',
            [
                'set' => $this->request->getParam('set')
            ]
        );
    }

    /**
     * @return bool
     */
    public function cleanCache()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (boolean)$this->_scopeConfigManager->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed|string
     */
    public function getFirstAttributeSet()
    {
        $default = '4';
        $activeAttributeSets = $this->getActiveAttributeSet();
        if (is_array($activeAttributeSets)) {
            $default = current($activeAttributeSets);
        }
        return $default;
    }

    /**
     * @return array|mixed
     */
    public function getActiveAttributeSet()
    {
        $val = $this->_scopeConfigManager->getValue(
            'ced_csmarketplace/general/set'
        );
        if ($val) {
            $val = explode(',', $val);
        }
        return $val;
    }
}
