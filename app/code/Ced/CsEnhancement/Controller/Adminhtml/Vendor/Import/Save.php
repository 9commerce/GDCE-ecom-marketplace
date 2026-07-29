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
 * @category  Ced
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;

use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class Save
 * @package Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class Save extends \Magento\Backend\App\Action
{
    const ATTRIBUTE_EMAIL = 'email';
    const ATTRIBUTE_WEBSITE_ID = 'website_id';
    const ATTRIBUTE_PUBLIC_NAME = 'public_name';
    const ATTRIBUTE_SHOP_URL = 'shop_url';
    const ATTRIBUTE_COUNTRY_ID = 'country_id';
    const ATTRIBUTE_REGION_ID = 'region_id';
    const ATTRIBUTE_REGION = 'region';
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollection;
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResourceModel;
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor
     */
    protected $vendorResourceModel;

    /**
     * Save constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResourceModel
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResourceModel,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->countryCollection = $countryCollection;
        $this->regionFactory = $regionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerResourceModel = $customerResourceModel;
        $this->vendorFactory = $vendorFactory;
        $this->vendorResourceModel = $vendorResourceModel;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $csvData = $this->getRequest()->getParam('import_data', []);
        $uniqueAttributes = $this->getRequest()->getParam('unique_attribute', '');
        $requiredAttributes = $this->getRequest()->getParam('required_attribute', '');

        if (!empty($csvData) && is_array($csvData)) {
            $result = $this->import(
                $csvData,
                explode(',', $uniqueAttributes),
                explode(',', $requiredAttributes)
            );
        } else {
            $result['errors'][] = __('Invalid Data Supplied');
            $this->messageManager->addErrorMessage(__('Invalid Data Supplied'));
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    /**
     * @param $csvData
     * @param array $uniqueAttributes
     * @param array $requiredAttributes
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($csvData, $uniqueAttributes = [], $requiredAttributes = [])
    {
        $result = ['errors' => [], 'success' => []];
        $successCount = 0;
        $countriesNames = $countriesIso3Code = [];
        /** @var \Magento\Directory\Model\ResourceModel\Country\Collection $countriesCollection */
        $countriesCollection = $this->countryCollection->create();
        $countriesCollection->loadByStore();
        /** @var \Magento\Directory\Model\Country $country */
        foreach ($countriesCollection as $country) {
            $countriesIso3Code[$country->getCountryId()] = $country->getData('iso3_code');
            $countriesNames[$country->getCountryId()] = strtoupper($country->getName()??'');
        }

        //get region country collection
        $regionCollection = $this->regionFactory->create()->getCollection()->addFieldToSelect('country_id');
        $regionCollection->getSelect()->group('country_id');
        $regionCountries = $regionCollection->getColumnValues('country_id');

        foreach ($csvData as $row => $rowData) {
            $error = $customer_id = 0;
            $rowData[self::ATTRIBUTE_PUBLIC_NAME] = $rowData[self::ATTRIBUTE_PUBLIC_NAME] ?? '';
            $rowData[self::ATTRIBUTE_SHOP_URL] = $this->setShopUrl(($rowData[self::ATTRIBUTE_SHOP_URL] ?? $rowData[self::ATTRIBUTE_PUBLIC_NAME]));
            $customerData = $rowData;

            //required check
            foreach ($requiredAttributes as $requiredAttribute) {
                if (empty($rowData[$requiredAttribute])) {
                    $result['errors'][] = __("Error Occurred in row %1 : value for %2 is required", $row, $requiredAttribute);
                    $this->messageManager->addErrorMessage(__("Error Occurred in row %1 : value for %2 is required", $row, $requiredAttribute));
                    $error++;
                }
            }

            //unique key check
            foreach ($uniqueAttributes as $uniqueAttribute) {
                if (array_key_exists($uniqueAttribute, $rowData) &&
                    $this->checkForUnique($uniqueAttribute, $rowData[$uniqueAttribute])) {
                    $result['errors'][] = __("Error Occurred in row %1 : value for %2 already exist", $row, $uniqueAttribute);
                    $this->messageManager->addErrorMessage(__("Error Occurred in row %1 : value for %2 already exist", $row, $uniqueAttribute));
                    $error++;
                }
            }

            //setCountry Info
            $country = isset($rowData[self::ATTRIBUTE_COUNTRY_ID]) ? strtoupper($rowData[self::ATTRIBUTE_COUNTRY_ID]) : '';
            $rowData[self::ATTRIBUTE_COUNTRY_ID] = $this->setCountryInfo(
                $country,
                $countriesIso3Code,
                $countriesNames
            );

            if (!empty($country) && empty($rowData[self::ATTRIBUTE_COUNTRY_ID])) {
                $result['errors'][] = __("Error Occurred in row %1 : Invalid value provided for %2", $row, self::ATTRIBUTE_COUNTRY_ID);
                $this->messageManager->addErrorMessage(__("Error Occurred in row %1 : Invalid value provided for %2", $row, self::ATTRIBUTE_COUNTRY_ID));
                $error++;
            }

            $rowData = $this->setRegionInfo($rowData);
            if (in_array(self::ATTRIBUTE_REGION_ID, $requiredAttributes) &&
                empty($rowData[self::ATTRIBUTE_REGION_ID]) &&
                in_array($rowData[self::ATTRIBUTE_COUNTRY_ID], $regionCountries)
            ) {
                $result['errors'][] = __(
                    "Error Occurred in row %1 : Invalid value provided for %2",
                    $row,
                    self::ATTRIBUTE_REGION_ID
                );
                $this->messageManager->addErrorMessage(
                    __(
                        "Error Occurred in row %1 : Invalid value provided for %2",
                        $row,
                        self::ATTRIBUTE_REGION_ID
                    )
                );
                $error++;
            }

            if (!$error && !empty($rowData[self::ATTRIBUTE_EMAIL]) && !empty($rowData[self::ATTRIBUTE_SHOP_URL])) {
                $customerExist = $this->checkCustomerExist(
                    $rowData[self::ATTRIBUTE_EMAIL],
                    (isset($rowData[self::ATTRIBUTE_WEBSITE_ID]) ?: 1)
                );

                if ($customerExist) {
                    unset($customerData[self::ATTRIBUTE_EMAIL]);
                    $customerModel = $customerExist;
                } else {
                    $customerModel = $this->customerFactory->create();
                }

                $customerModel->addData($customerData);
                try {
                    $this->customerResourceModel->save($customerModel);
                    $customer_id = $customerModel->getId();
                } catch (\Exception $e) {
                    $result['errors'][] = __("Error Occurred in row %1 : ", $row) . $e->getMessage();
                }

                if (!empty($customer_id)) {
                    $vendorModel = $this->vendorFactory->create();
                    $vendorModel->setCustomer($customerModel);

                    try {
                        $vendorModel->register($rowData);
                        if ($vendorModel->getData('errors')) {
                            foreach ($vendorModel->getData('errors') as $error) {
                                $e = (!empty($e)) ? $e . '<br>' . $error : $error;
                            }
                            throw new \Exception((!empty($e) ? $e : __('An error occurred while saving vendor on row %1.', $row)));
                        }

                        $this->vendorResourceModel->save($vendorModel);
                        $successCount++;
                    } catch (AlreadyExistsException | \Exception $e) {
                        $result['errors'][] = __("Error Occurred in row %1 : ", $row) . $e->getMessage();
                        $this->messageManager->addErrorMessage(__("Error Occurred in row : %1", $row) . $e->getMessage());
                    }
                }
            }
        }

        if ($successCount > 0) {
            $result['success'][] = __('A total of %1 record(s) has been successfully imported', $successCount);
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) has been successfully imported', $successCount));
        }

        return $result;
    }

    /**
     * @param $shop_url
     * @return string|string[]|null
     */
    protected function setShopUrl($shop_url)
    {
        // Replaces all spaces with hyphens.
        $shop_url = str_replace(' ', '-', strtolower($shop_url));

        // Removes special chars.
        $shop_url = preg_replace('/[^A-Za-z0-9\-]/', '', $shop_url);
        // Replaces multiple hyphens with single one.
        $shop_url = preg_replace('/-+/', '-', $shop_url);
        return $shop_url;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkForUnique($attribute, $value)
    {
        $vendorCollection = $this->vendorCollectionFactory->create();
        $vendorCollection->addAttributeToFilter($attribute, ['eq' => $value]);

        return ($vendorCollection && $vendorCollection->getSize() > 0) ?: false;
    }

    /**
     * @param $country_id
     * @param array $countriesIso3Code
     * @param array $countriesNames
     * @return false|int|string
     */
    protected function setCountryInfo($country_id = '', $countriesIso3Code = [], $countriesNames = [])
    {
        switch (strlen($country_id)) {
            case 0:
                break;

            case 2:
                if (array_key_exists($country_id, $countriesIso3Code)) {
                    return $country_id;
                }
                break;

            case 3:
                $key = array_search($country_id, $countriesIso3Code);
                if ($key) {
                    return $key;
                }
                break;

            default:
                $key = array_search($country_id, $countriesIso3Code);
                if ($key) {
                    return $key;
                }
                break;
        }

        return '';
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function setRegionInfo($rowData)
    {
        if (!empty($rowData[self::ATTRIBUTE_COUNTRY_ID])) {
            //get Region code
            $region_id = $rowData[self::ATTRIBUTE_REGION_ID] ?? '';
            $region = !empty($rowData[self::ATTRIBUTE_REGION]) ? $rowData[self::ATTRIBUTE_REGION] : $region_id;

            unset($rowData[self::ATTRIBUTE_REGION_ID]);
            if ($region) {
                switch (strlen($region)) {
                    case 2:
                        $regionModel = $this->regionFactory->create()->loadByCode(
                            $region,
                            $rowData[self::ATTRIBUTE_COUNTRY_ID]
                        );
                        break;
                    default:
                        $regionModel = $this->regionFactory->create()->loadByName(
                            $region,
                            $rowData[self::ATTRIBUTE_COUNTRY_ID]
                        );
                        break;
                }

                if ($regionModel->getId()) {
                    $rowData[self::ATTRIBUTE_REGION_ID] = $regionModel->getId();
                    unset($rowData[self::ATTRIBUTE_REGION]);
                }
            }
        }

        return $rowData;
    }

    /**
     * @param $email
     * @param null $website_id
     * @return bool|\Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkCustomerExist($email, $website_id = null)
    {
        $customer = $this->customerFactory->create();

        if ($website_id) {
            $customer->setWebsiteId($website_id);
        }

        $customer->loadByEmail($email);
        return ($customer->getEmail()) ? $customer : false;
    }
}
