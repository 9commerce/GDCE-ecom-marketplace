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
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Adminhtml\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\AddressRegistry;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;

/**
 * Save customer action.
 * Class Save
 * @package Ced\CsMarketplace\Controller\Adminhtml\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $filEFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $forMFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $randoM
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addreSSMapper
     * @param AccountManagementInterface $customerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerInterfaceFactory $customeRDataFactory
     * @param AddressInterfaceFactory $cedAddressDataFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $datAObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layouTFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resulTForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param AddressRegistry|null $addreSSRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $filEFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $forMFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $randoM,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addreSSMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customeRDataFactory,
        AddressInterfaceFactory $cedAddressDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $datAObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layouTFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resulTForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ?AddressRegistry $addreSSRegistry = null
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $filEFactory,
            $customerFactory,
            $addressFactory,
            $forMFactory,
            $subscriberFactory,
            $viewHelper,
            $randoM,
            $customerRepository,
            $extensibleDataObjectConverter,
            $addreSSMapper,
            $customerAccountManagement,
            $addressRepository,
            $customeRDataFactory,
            $cedAddressDataFactory,
            $customerMapper,
            $datAObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layouTFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resulTForwardFactory,
            $resultJsonFactory
        );
        $this->addressRegistry = $addreSSRegistry ?: ObjectManager::getInstance()
            ->get(AddressRegistry::class);
    }

    /**
     * Reformat customer account data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerData()
    {
        $customerData = [];
        if ($this->getRequest()->getPost('customer')) {
            $additionalAttributes = [
                CustomerInterface::DEFAULT_BILLING,
                CustomerInterface::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail_store_id',
                'extension_attributes',
            ];

            $customerData = $this->_extractData(
                'adminhtml_customer',
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $additionalAttributes,
                'customer'
            );
        }

        if (isset($customerData['disable_auto_group_change'])) {
            $customerData['disable_auto_group_change'] = (int) filter_var(
                $customerData['disable_auto_group_change'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $customerData;
    }

    /**
     * Perform customer data filtration based on form code and form object
     *
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @return array
     */
    protected function _extractData(
        $formCode,
        $entityType,
        $additionalAttributes = [],
        $scope = null
    ) {
        $metadataForm = $this->getMetadataForm($entityType, $formCode, $scope);
        $cedFormData = $metadataForm->extractData($this->getRequest(), $scope);
        $cedFormData = $metadataForm->compactData($cedFormData);

        // Initialize additional attributes
        /** @var \Magento\Framework\DataObject $object */
        $object = $this->_objectFactory->create(['data' => $this->getRequest()->getPostValue()]);
        $requestData = $object->getData($scope);
        foreach ($additionalAttributes as $cedAttributeCode) {
            $cedFormData[$cedAttributeCode] = isset($requestData[$cedAttributeCode]) ?
                $requestData[$cedAttributeCode] : false;
        }

        // Unset unused attributes
        $formAttributes = $metadataForm->getAttributes();
        foreach ($formAttributes as $attribute) {
            /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
            $cedAttributeCode = $attribute->getAttributeCode();
            if ($attribute->getFrontendInput() != 'boolean'
                && $cedFormData[$cedAttributeCode] === false
            ) {
                unset($cedFormData[$cedAttributeCode]);
            }
        }

        if (empty($cedFormData['extension_attributes'])) {
            unset($cedFormData['extension_attributes']);
        }

        return $cedFormData;
    }

    /**
     * Saves default_billing and default_shipping flags for customer address
     *
     * @deprecated 102.0.1 must be removed because addresses are save separately for now
     * @param array $cedAddressIdList
     * @param array $extractedCustomerData
     * @return array
     */
    protected function saveDefaultFlags(array $cedAddressIdList, array & $extractedCustomerData)
    {
        $result = [];
        $extractedCustomerData[CustomerInterface::DEFAULT_BILLING] = null;
        $extractedCustomerData[CustomerInterface::DEFAULT_SHIPPING] = null;
        foreach ($cedAddressIdList as $cedAddressId) {
            $scope = sprintf('address/%s', $cedAddressId);
            $cedAddressData = $this->_extractData(
                'adminhtml_customer_address',
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                ['default_billing', 'default_shipping'],
                $scope
            );

            if (is_numeric($cedAddressId)) {
                $cedAddressData['id'] = $cedAddressId;
            }
            // Set default billing and shipping flags to customer
            if (!empty($cedAddressData['default_billing']) && $cedAddressData['default_billing'] === 'true') {
                $extractedCustomerData[CustomerInterface::DEFAULT_BILLING] = $cedAddressId;
                $cedAddressData['default_billing'] = true;
            } else {
                $cedAddressData['default_billing'] = false;
            }
            if (!empty($cedAddressData['default_shipping']) && $cedAddressData['default_shipping'] === 'true') {
                $extractedCustomerData[CustomerInterface::DEFAULT_SHIPPING] = $cedAddressId;
                $cedAddressData['default_shipping'] = true;
            } else {
                $cedAddressData['default_shipping'] = false;
            }
            $result[] = $cedAddressData;
        }
        return $result;
    }

    /**
     * Reformat customer addresses data to be compatible with customer service interface
     *
     * @deprecated 102.0.1 addresses are saved separately for now
     * @param array $extractedCustomerData
     * @return array
     */
    protected function _extractCustomerAddressData(array & $extractedCustomerData)
    {
        $addresses = $this->getRequest()->getPost('address');
        $result = [];
        if (is_array($addresses)) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            $cedAddressIdList = array_keys($addresses);
            $result = $this->saveDefaultFlags($cedAddressIdList, $extractedCustomerData);
        }

        return $result;
    }

    /**
     * Save customer action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $cedReturnToEdit = false;
        $ccedCustomerId = $this->getCurrentCustomerId();

        if ($this->getRequest()->getPostValue()) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $customerData = $this->_extractCustomerData();
                $currentCustomerEmail = '';
                if ($ccedCustomerId) {
                    $currentCustomer = $this->_customerRepository->getById($ccedCustomerId);
                    // No need to validate customer address while editing customer profile
                    $this->disableAddressValidation($currentCustomer);
                    $customerData = array_merge(
                        $this->customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $ccedCustomerId;
                    $currentCustomerEmail = $currentCustomer->getEmail();
                }

                /** @var CustomerInterface $customer */
                $customer = $this->customerDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );

                $this->_eventManager->dispatch(
                    'adminhtml_customer_prepare_save',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );

                if (isset($customerData['sendemail_store_id'])) {
                    $customer->setStoreId($customerData['sendemail_store_id']);
                }

                // Save customer
                if ($ccedCustomerId) {
                    $this->_customerRepository->save($customer);
                    $this->getEmailNotification()->credentialsChanged($customer, $currentCustomerEmail);
                } else {
                    $customer = $this->customerAccountManagement->createAccount($customer);
                    $ccedCustomerId = $customer->getId();
                }

                $isSubscribed = null;
                if ($this->_authorization->isAllowed(null)) {
                    $isSubscribed = $this->getRequest()->getPost('subscription');
                }
                if ($isSubscribed !== null) {
                    if ($isSubscribed !== '0') {
                        $this->_subscriberFactory->create()->subscribeCustomerById($ccedCustomerId);
                    } else {
                        $this->_subscriberFactory->create()->unsubscribeCustomerById($ccedCustomerId);
                    }
                }

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_customer_save_after',
                    ['customer' => $customer, 'request' => $this->getRequest()]
                );
                $this->_getSession()->unsCustomerFormData();
                // Done Saving customer, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $ccedCustomerId);
                $this->messageManager->addSuccessMessage(__('You saved the customer.'));
                $cedReturnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData());
                $cedReturnToEdit = true;
            } catch (\Magento\Framework\Exception\AbstractAggregateException $exception) {
                $errors = $exception->getErrors();
                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData());
                $cedReturnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData());
                $cedReturnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage($exception, __('Something went wrong while saving the customer.'));
                $this->_getSession()->setCustomerFormData($this->retrieveFormattedFormData());
                $cedReturnToEdit = true;
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $pattern = 'ced_vendor/1';
        $cedVendor = strpos($url, $pattern);
        if ($cedVendor){
            $resultRedirect->setPath('csmarketplace/vendor/add');
        }else if ($cedReturnToEdit) {
            if ($ccedCustomerId) {
                $resultRedirect->setPath(
                    'customer/*/edit',
                    ['id' => $ccedCustomerId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'customer/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('customer/index');
        }
        return $resultRedirect;
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get metadata form
     *
     * @param string $entityType
     * @param string $formCode
     * @param string $scope
     * @return Form
     */
    private function getMetadataForm($entityType, $formCode, $scope)
    {
        $attributeValues = [];

        if ($entityType == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $ccedCustomerId = $this->getCurrentCustomerId();
            if ($ccedCustomerId) {
                $customer = $this->_customerRepository->getById($ccedCustomerId);
                $attributeValues = $this->customerMapper->toFlatArray($customer);
            }
        }

        if ($entityType == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $scopeData = explode('/', $scope);
            if (isset($scopeData[1]) && is_numeric($scopeData[1])) {
                $customerAddress = $this->addressRepository->getById($scopeData[1]);
                $attributeValues = $this->addressMapper->toFlatArray($customerAddress);
            }
        }

        $metadataForm = $this->_formFactory->create(
            $entityType,
            $formCode,
            $attributeValues,
            false,
            Form::DONT_IGNORE_INVISIBLE
        );

        return $metadataForm;
    }

    /**
     * Retrieve current customer ID
     *
     * @return int
     */
    private function getCurrentCustomerId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $ccedCustomerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $ccedCustomerId;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Retrieve formatted form data
     *
     * @return array
     */
    private function retrieveFormattedFormData(): array
    {
        $originalRequestData = $this->getRequest()->getPostValue();

        /* Customer data filtration */
        if (isset($originalRequestData['customer'])) {
            $customerData = $this->_extractData(
                'adminhtml_customer',
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                [],
                'customer'
            );

            $customerData = array_intersect_key($customerData, $originalRequestData['customer']);
            $originalRequestData['customer'] = array_merge($originalRequestData['customer'], $customerData);
        }

        return $originalRequestData;
    }
}
