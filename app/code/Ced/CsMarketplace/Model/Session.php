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

namespace Ced\CsMarketplace\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;


/**
 * CsMarketplace session model
 */
class Session extends \Magento\Customer\Model\Session
{

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * Session constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Customer\Model\Config\Share $configShare
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param ResourceCustomer $customerResource
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\App\Response\Http $response
     * @param VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Customer\Model\Config\Share $configShare,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \Magento\Customer\Model\Url $customerUrl,
        ResourceCustomer $customerResource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        CustomerRepositoryInterface $customerRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\App\Response\Http $response,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    ) {
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $configShare,
            $coreUrl,
            $customerUrl,
            $customerResource,
            $customerFactory,
            $urlFactory,
            $session,
            $eventManager,
            $httpContext,
            $customerRepository,
            $groupManagement,
            $response
        );

        $this->_sessionFactory = $sessionFactory;
        $this->_vendorFactory = $vendorFactory;
    }

    /**
     * @return $this
     */
    public function getVendor()
    {
        return $this->_vendorFactory->create()->load($this->getVendorId());
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->getCustomerSession()->getVendorId();
    }

    /**
     * @return \Magento\Framework\Session\Generic
     */
    public function getCustomerSession()
    {
        if (!$this->_session->getCustomerId()) {
            $this->_session = $this->_sessionFactory->create();
        }
        return $this->_session;
    }
}
