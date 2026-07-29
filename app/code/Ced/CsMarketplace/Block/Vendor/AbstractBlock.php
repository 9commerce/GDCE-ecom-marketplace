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

namespace Ced\CsMarketplace\Block\Vendor;

use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class AbstractBlock
 * @package Ced\CsMarketplace\Block\Vendor
 */
class AbstractBlock extends Template
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var UrlFactory
     */
    protected $urlModel;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var null
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Customer\Model\Customer|null
     */
    protected $_customerFactory;

    /**
     * @var null
     */
    protected $_vendorUrl;

    /**
     * AbstractBlock constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->urlModel = $urlFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->session = $customerSession->getCustomerSession();
        parent::__construct($context);
    }

    /**
     * Retrieve customer session model object
     *
     * @return Session|\Magento\Framework\Session\Generic
     */
    public function _getSession()
    {
        return $this->session;
    }

    /**
     * Retrieve Store
     *
     * @param int
     * @return \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore($storeId = null)
    {
        if ($storeId) {
            $this->getStoreManager()->getStore($storeId);
        }
        return $this->getStoreManager()->getStore();
    }

    /**
     * Retrieve StoreManagerInterface
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * Get vendor
     *
     * @return \Ced\CsMarketplace\Model\Vendor
     */
    public function getVendor()
    {
        return $this->_vendorFactory->create()->load($this->getVendorId());
    }

    /**
     * Get vendor ID
     *
     * @return int
     */
    public function getVendorId()
    {
        return $this->session->getVendorId();
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->_customerFactory->create()->load($this->getCustomerId());
    }

    /**
     * Get customer ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->session->getCustomerId();
    }

    /**
     * Get vendor url in vendor dashboard
     *
     * @return string
     */
    public function getVendorUrl()
    {
        if ($this->_vendorUrl == null) {
            $this->_vendorUrl = $this->urlModel->create()->getUrl(
                'csmarketplace/vendor/edit',
                ['_secure' => true]
            );
        }
        return $this->_vendorUrl;
    }

    /**
     * Get back url in vendor dashboard
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->urlModel->create()->getUrl('csmarketplace/vendor/', ['_secure' => true]);
    }
}
