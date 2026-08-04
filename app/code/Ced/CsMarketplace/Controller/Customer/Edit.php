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
namespace Ced\CsMarketplace\Controller\Customer;

use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Customer\Helper\View as CustomerHelperView;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Controller\RegistryConstants;
class Edit extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var mixed
     */
    protected $customerSession;

    /**
     * @var mixed
     */
    protected $backendSession;

    /**
     * @var mixed
     */
    protected $customerMapper;

    /**
     * @var mixed
     */
    protected $addressMapper;

    /**
     * @var mixed
     */
    protected $_viewHelper;

    /**
     * @var mixed
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Country constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection
     */
    public function __construct(
    	Context $context,
        UrlFactory $urlFactory,
        Registry $coreRegistry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        AddressMapper $addressMapper,
        BackendSession $backendSession,
        CustomerHelperView $viewHelper

    ) {
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerMapper = $customerMapper;
        $this->addressMapper = $addressMapper;
        $this->_viewHelper = $viewHelper;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $coreRegistry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Forgot customer account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $customerData = [];
        $customerData['account'] = [];
        $customerData['address'] = [];
        $customer = null;
        $customerId = $this->initCurrentCustomer();
        $isExistingCustomer = (bool)$customerId;
        if ($isExistingCustomer && $this->csmarketplaceHelper->canHideCustomerDetails()) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customerData['account'] = $this->customerMapper->toFlatArray($customer);
                $customerData['account'][CustomerInterface::ID] = $customerId;
                try {
                    $addresses = $customer->getAddresses();
                    foreach ($addresses as $address) {
                        $customerData['address'][$address->getId()] = $this->addressMapper->toFlatArray($address);
                        $customerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the customer.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }
        }else{
            $this->messageManager->addError(__('Something Went Wrong'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('csmarketplace');
            return $resultRedirect;
        }
        $customerData['customer_id'] = $customerId;
        $this->backendSession->setCustomerData($customerData);
        if ($isExistingCustomer) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getCustomerName($customer));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Customer'));
        }
       

        return $resultPage;
    }

    public function initCurrentCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        if ($customerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        return $customerId;
    }
}
