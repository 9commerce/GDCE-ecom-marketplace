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

namespace Ced\CsEnhancement\Model\Import;

use Ced\CsEnhancement\Logger\Logger;
use Magento\Framework\Validator\ValidatorChain;

/**
 * Class Vendor
 * @package Ced\CsEnhancement\Model\Import
 */
class Vendor extends \Magento\ImportExport\Model\Import\Entity\AbstractEav
{
    const COLUMN_EMAIL = 'email';

    const COLUMN_STORE = '_store';

    const COLUMN_WEBSITE = '_website';

    /**#@-*/

    /**#@+
     * Keys which used to build result data array for future update
     */
    const ENTITIES_TO_CREATE_KEY = 'entities_to_create';

    const ATTRIBUTES_TO_SAVE_KEY = 'attributes_to_save';

    /**#@-*/

    /**#@+
     * Error codes
     */
    const ERROR_DUPLICATE_EMAIL_SITE = 'duplicateEmailSite';

    const ERROR_INVALID_STORE = 'invalidStore';

    const ERROR_WEBSITE_IS_EMPTY = 'websiteIsEmpty';

    const ERROR_EMAIL_IS_EMPTY = 'emailIsEmpty';

    const ERROR_INVALID_WEBSITE = 'invalidWebsite';

    const ERROR_INVALID_EMAIL = 'invalidEmail';

    const ERROR_VALUE_IS_REQUIRED = 'valueIsRequired';

    const ERROR_CUSTOMER_NOT_FOUND = 'customerNotFound';

    /**#@-*/

    /**
     * Array of attribute codes which will be ignored in validation and import procedures.
     * For example, when entity attribute has own validation and import procedures
     * or just to deny this attribute processing.
     *
     * @var string[]
     */
    protected $_ignoredAttributes = ['website_id', 'store_id'];

    /**
     * Customers information from import file
     *
     * @var array
     */
    protected $_newCustomers = [];

    /**
     * Id of next customer entity row
     *
     * @var int
     */
    protected $_nextEntityId;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Vendor constructor.
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->_attributeCollection->getEntityTypeCode();
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @throws \Zend_Validate_Exception
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            $email = strtolower($rowData[self::COLUMN_EMAIL]);
            $website = $rowData[self::COLUMN_WEBSITE];

            if (isset($this->_newCustomers[strtolower($rowData[self::COLUMN_EMAIL])][$website])) {
                $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNumber);
            }
            $this->_newCustomers[$email][$website] = false;

            if (!empty($rowData[self::COLUMN_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
            }

            // check simple attributes
            foreach ($this->_attributes as $attributeCode => $attributeParams) {
                if (in_array($attributeCode, $this->_ignoredAttributes)) {
                    continue;
                }
                if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                    $this->isAttributeValid(
                        $attributeCode,
                        $attributeParams,
                        $rowData,
                        $rowNumber,
                        ','
                    );
                } elseif ($attributeParams['is_required']) {
                    $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                }
            }
        }
    }

    /**
     * General check of unique key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    protected function _checkUniqueKey(array $rowData, $rowNumberVendor)
    {
        if (empty($rowData[static::COLUMN_WEBSITE])) {
            $this->addRowError(static::ERROR_WEBSITE_IS_EMPTY, $rowNumberVendor, static::COLUMN_WEBSITE);
        } elseif (empty($rowData[static::COLUMN_EMAIL])) {
            $this->addRowError(static::ERROR_EMAIL_IS_EMPTY, $rowNumberVendor, static::COLUMN_EMAIL);
        } else {
            $email = strtolower($rowData[static::COLUMN_EMAIL]);
            $website = $rowData[static::COLUMN_WEBSITE];

            if (!ValidatorChain::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->addRowError(static::ERROR_INVALID_EMAIL, $rowNumberVendor, static::COLUMN_EMAIL);
            } elseif (!isset($this->_websiteCodeToId[$website])) {
                $this->addRowError(static::ERROR_INVALID_WEBSITE, $rowNumberVendor, static::COLUMN_WEBSITE);
            }
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumberVendor);
    }

    /**
     * Import data rows
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importData()
    {
        if (!empty($bunch)) {
            $entitiesToCreate = [];
            $attributesToSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $this->logger->info('------------_prepareDataForUpdate----------');

                $processedData = $this->_prepareDataForUpdate($rowData);

                $this->logger->info('------------$processedData----------', $processedData);

                $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);

                $this->logger->info('------------$entitiesToCreate----------', $entitiesToCreate);

                foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                    if (!isset($attributesToSave[$tableName])) {
                        $attributesToSave[$tableName] = [];
                    }
                    $attributesToSave[$tableName] = array_diff_key(
                        $attributesToSave[$tableName],
                        $customerAttributes
                    ) + $customerAttributes;
                }

                $this->logger->info('------------$attributesToSave----------', $attributesToSave);
            }

            $this->updateItemsCounterStats($entitiesToCreate);

            /**
             * Save prepared data
             */
            if (!empty($entitiesToCreate)) {
                $this->_saveCustomerEntities($entitiesToCreate);
            }
            if ($attributesToSave) {
                $this->_saveCustomerAttributes($attributesToSave);
            }
        }

        return true;
    }

    /**
     * Prepare customer data for update
     *
     * @param array $rowData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $multiSeparator = $this->getMultipleValueSeparator();
        $entitiesToCreate = [];
        $attributesToSave = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $emailInLowercase = strtolower($rowData[self::COLUMN_EMAIL]);
        $entityId = $this->_getNextEntityId();
        $this->_newCustomers[$emailInLowercase][$rowData[self::COLUMN_WEBSITE]] = $entityId;

        $this->logger->info('------------$entityId-------------------' . $entityId);

        $entityRow = ['entity_id' => $entityId];
        // attribute values
        foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $value) {
            $this->logger->info('------------$attributeCode =  ' . $attributeCode);
            $this->logger->info('------------$attributeCode $value = ' . $value);

            $attributeParameters = $this->_attributes[$attributeCode];
            $this->logger->info('------------$attributeParameters = ', $attributeParameters);

            if (in_array($attributeParameters['type'], ['select', 'boolean'])) {
                $value = $this->getSelectAttrIdByValue($attributeParameters, $value);
            } elseif ('multiselect' == $attributeParameters['type']) {
                $ids = [];
                foreach (explode($multiSeparator, mb_strtolower($value)) as $subValue) {
                    $ids[] = $this->getSelectAttrIdByValue($attributeParameters, $subValue);
                }
                $value = implode(',', $ids);
            } elseif ('datetime' == $attributeParameters['type'] && !empty($value)) {
                $value = (new \DateTime())->setTimestamp(strtotime($value));
                $value = $value->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }

            if (!$this->_attributes[$attributeCode]['is_static']) {
                $this->logger->info('------------!is_static $attributeCode = ' . $attributeCode);

                /** @var $attribute \Magento\Customer\Model\Attribute */
                $attribute = $this->_customerModel->getAttribute($attributeCode);
                $backendModel = $attribute->getBackendModel();

                $this->logger->info('------------!is_static $backendModel = ' . $backendModel);

                if ($backendModel
                    && $attribute->getFrontendInput() != 'select'
                    && $attribute->getFrontendInput() != 'datetime') {
                    $attribute->getBackend()->beforeSave($this->_customerModel->setData($attributeCode, $value));
                    $value = $this->_customerModel->getData($attributeCode);

                    $this->logger->info('------------!is_static $backendModel$value = ' . $value);
                }

                $attributesToSave[$attribute->getBackend()
                    ->getTable()][$entityId][$attributeParameters['id']] = $value;

                $this->logger->info('----------!is_static $backendModel table = ' . $attribute->getBackend()->getTable());
                $this->logger->info('----------!is_static $backendModel val = ' . $value);

                // restore 'backend_model' to avoid default setting
                $attribute->setBackendModel($backendModel);
            } else {
                $entityRow[$attributeCode] = $value;

                $this->logger->info('------------$entityRow $attributeCode = ' . $attributeCode);
                $this->logger->info('------------$entityRow[$attributeCode] = ' . $entityRow[$attributeCode]);
            }
        }

        if ($entityId) {
            // create
            $entityRow['group_id'] = empty($rowData['group_id']) ? self::DEFAULT_GROUP_ID : $rowData['group_id'];
            $entityRow['store_id'] = empty($rowData[self::COLUMN_STORE])
                ? 0 : $this->_storeCodeToId[$rowData[self::COLUMN_STORE]];
            $entityRow['created_at'] = $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $entityRow['updated_at'] = $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $entityRow['website_id'] = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];
            $entityRow['email'] = $emailInLowercase;
            $entityRow['is_active'] = 1;
            $entitiesToCreate[] = $entityRow;
        }

        $this->logger->info('------------$entityRow = ', $entityRow);

        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ATTRIBUTES_TO_SAVE_KEY => $attributesToSave
        ];
    }

    /**
     * Retrieve next customer entity id
     *
     * @return int
     */
    protected function _getNextEntityId()
    {
        if (!$this->_nextEntityId) {
            $this->_nextEntityId = $this->_resourceHelper->getNextAutoincrement($this->_entityTable);
        }
        return $this->_nextEntityId++;
    }
}
