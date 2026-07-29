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

namespace Ced\CsMarketplace\Model\Adminhtml\Config;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\Registry;
use Ced\CsMarketplace\Model\Adminhtml\Config\ConfigObjects;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;

/**
 * Class Data
 * @package Ced\CsMarketplace\Model\Adminhtml\Config
 */
class Data extends \Magento\Config\Model\Config
{

    /**
     * Config data for sections
     *
     * @var array
     */
    protected $_configData;

    /**
     * Event dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * System configuration structure
     *
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * Application configit
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfig;

    /**
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Config data loader
     *
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $_configLoader;

    /**
     * Config data factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var
     */
    protected $_request;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_httpRequest;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Config\Model\Config\Loader $configLoader
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param SettingChecker|null $settingChecker
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        Registry $registry,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Config\Model\Config\Loader $configLoader,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ?SettingChecker $settingChecker = null,
        array $data = []
    ) {
        parent::__construct($config, $eventManager, $configStructure, $transactionFactory, $configLoader,
            $configValueFactory, $storeManager, $settingChecker, $data);
        $this->_coreRegistry = $registry;
        $this->_moduleManager = $moduleManager;
        $this->_httpRequest = $httpRequest;
        $this->_vendorFactory = $vendorFactory;
    }

    /**
     * @param string $groupId
     * @param array $groupData
     * @param array $groups
     * @param string $sectionPath
     * @param array $extraOldGroups
     * @param array $oldConfig
     * @param \Magento\Framework\DB\Transaction $saveTransaction
     * @param \Magento\Framework\DB\Transaction $deleteTransaction
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _processGroup(
        $groupId,
        array $groupData,
        array $groups,
        $sectionPath,
        array &$extraOldGroups,
        array &$oldConfig,
        \Magento\Framework\DB\Transaction $saveTransaction,
        \Magento\Framework\DB\Transaction $deleteTransaction
    ) {
        $groupPath = $sectionPath . '/' . $groupId;
        $scope = $this->getScope();
        $scopeId = $this->getScopeId();
        $scopeCode = $this->getScopeCode();

        /* Map field names if they were cloned */
        /** @var \Magento\Config\Model\Config\Structure\Element\Group $group */
        $group = $this->_configStructure->getElement($groupPath);

        // set value for group field entry by fieldname
        // use extra memory
        $fieldsetData = [];
        $mappedFields = [];
        if (isset($groupData['fields'])) {
            if ($group->shouldCloneFields()) {
                $cloneModel = $group->getCloneModel();

                /** @var Field $field */
                foreach ($group->getChildren() as $field) {
                    foreach ($cloneModel->getPrefixes() as $prefix) {
                        $mappedFields[$prefix['field'] . $field->getId()] = $field->getId();
                    }
                }
            }
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = is_array(
                    $fieldData
                ) && isset(
                    $fieldData['value']
                ) ? $fieldData['value'] : null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $originalFieldId = $fieldId;
                if ($group->shouldCloneFields() && isset($mappedFields[$fieldId])) {
                    $originalFieldId = $mappedFields[$fieldId];
                }
                /** @var Field $field */
                $field = $this->_configStructure->getElement($groupPath . '/' . $originalFieldId);

                /** @var \Magento\Framework\App\Config\ValueInterface $backendModel */
                $backendModel = $field->hasBackendModel() ? $field
                    ->getBackendModel() : $this
                    ->_configValueFactory
                    ->create();

                $data = [
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'scope_code' => $scopeCode,
                    'field_config' => $field->getData(),
                    'fieldset_data' => $fieldsetData
                ];


                $backendModel->addData($data);

                $this->_checkSingleStoreMode($field, $backendModel);

                if (false == isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $path = $field->getGroupPath() . '/' . $fieldId;
                $vendorId = $this->_coreRegistry->registry('data_com');

                /**
                 * Look for custom defined field path
                 */
                if ($field && $field->getConfigPath()) {
                    $configPath = $field->getConfigPath();
                    if (!empty($configPath) && strrpos($configPath, '/') > 0) {
                        // Extend old data with specified section group
                        $configGroupPath = substr($configPath, 0, strrpos($configPath, '/'));
                        if ($this->_moduleManager->isEnabled('Ced_CsCommission')) {
                            if ($vendorId) {
                                $configGroupPath = 'v' . $vendorId . '/' . $configGroupPath;
                            }
                        }
                        if (!isset($extraOldGroups[$configGroupPath])) {
                            $oldConfig = $this->extendConfig($configGroupPath, true, $oldConfig);
                            $extraOldGroups[$configGroupPath] = true;
                        }
                        $path = $configPath;

                    }
                }

                $inherit = !empty($fieldData['inherit']);
                $oldpath = $path;
                $vendorId = $this->_coreRegistry->registry('data_com');

                if ($vendorId) {
                    $path = 'v' . $vendorId . '/' . $path;
                }

                $groupDatas = $this->_httpRequest->getPost();
                $gcode = isset($groupDatas['group_code']) && strlen($groupDatas['group_code']) > 0 ?
                    $groupDatas['group_code'] :
                    ($this->_httpRequest->getParam('gcode', false) ? $this->_httpRequest->getParam('gcode') : '');
                if (strlen($gcode) > 0) {
                    $path = $gcode . '/' . $path;
                }

                $backendModel->setPath($path)->setValue($fieldData['value']);
                if (isset($oldConfig[$path])) {
                    $backendModel->setConfigId($oldConfig[$path]['config_id']);

                    /* Delete config data if inherit */
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                }

                if (isset($oldConfig[$oldpath])) {
                    $backendModel->setConfigId($oldConfig[$oldpath]['config_id']);

                    /* Delete config data if inherit */
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                } elseif (!$inherit) {
                    $backendModel->unsConfigId();
                    $saveTransaction->addObject($backendModel);
                }
                $deleteTransaction->delete();
                $saveTransaction->save();
            }
        }

        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $this->_processGroup(
                    $subGroupId,
                    $subGroupData,
                    $groups,
                    $groupPath,
                    $extraOldGroups,
                    $oldConfig,
                    $saveTransaction,
                    $deleteTransaction
                );
            }
        }

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load()
    {
        $is_csgroup = $this->_httpRequest->getParam('is_csgroup');
        if (!$is_csgroup) return parent::load();
        $this->initScope();
        $this->_configData = $this->_getConfig(false);
        return $this->_configData;
    }

    /**
     * @param string $path
     * @param bool $full
     * @param array $oldConfig
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = [])
    {
        $extended = $this->getConfigByPath($path, $this->getScope(), $this->getScopeId(), $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * Add data by path section/group/field
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @throws \UnexpectedValueException
     */
    public function setDataByPath($path, $value)
    {
        $path = trim($path);
        if ($path === '') {
            throw new \UnexpectedValueException('Path must not be empty');
        }
        $pathParts = explode('/', $path);
        $keyDepth = count($pathParts);
        if ($keyDepth !== 3) {
            throw new \UnexpectedValueException(
                "Allowed depth of configuration is 3 (<section>/<group>/<field>). Your configuration depth is "
                . $keyDepth . " for path '$path'"
            );
        }
        $data = [
            'section' => $pathParts[0],
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => ['value' => $value],
                    ],
                ],
            ],
        ];
        $this->addData($data);
    }


    /**
     * Get scope name and scopeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initScope()
    {
        $is_csgroup = $this->_httpRequest->getParam('is_csgroup');
        if (!$is_csgroup) {
            if ($this->getSection() === null)
                $this->setSection('');

            if ($this->getWebsite() === null)
                $this->setWebsite('');

            if ($this->getStore() === null)
                $this->setStore('');

            if ($this->getStore()) {
                $scope = 'stores';
                $store = $this->_storeManager->getStore($this->getStore());
                $scopeId = (int)$store->getId();
                $scopeCode = $store->getCode();
            } elseif ($this->getWebsite()) {
                $scope = 'websites';
                $website = $this->_storeManager->getWebsite($this->getWebsite());
                $scopeId = (int)$website->getId();
                $scopeCode = $website->getCode();
            } else {
                $scope = 'default';
                $scopeId = 0;
                $scopeCode = '';
            }
            $this->setScope($scope);
            $this->setScopeId($scopeId);
            $this->setScopeCode($scopeCode);
        } else {
            if ($this->getStore()) {
                $scope = 'stores';
                $store = $this->_storeManager->getStore($this->getStore());
                $scopeId = (int)$store->getId();
                $scopeCode = $store->getCode();
            } elseif ($this->getWebsite()) {
                $scope = 'websites';
                $website = $this->_storeManager->getWebsite($this->getWebsite());
                $scopeId = (int)$website->getId();
                $scopeCode = $website->getCode();
            } else {
                $scope = 'default';
                $scopeId = 0;
                $scopeCode = '';
            }
            $this->setScope($scope);
            $this->setScopeId($scopeId);
            $this->setScopeCode($scopeCode);
        }

    }

    /**
     * Return formatted config data for current section
     *
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getConfig($full = true)
    {
        $is_csgroup = $this->_httpRequest->getParam('is_csgroup');
        if (!$is_csgroup) return parent::_getConfig($full);
        $groupData = $this->_httpRequest->getPost();
        $gcode = isset($groupData['group_code']) && strlen($groupData['group_code']) > 0 ?
            $groupData['group_code'] : ($this->_httpRequest->getParam('gcode', false) ?
                $this->_httpRequest->getParam('gcode') : '');
        if (strlen($gcode) > 0) {
            return $this->getConfigByPath($this->getSection(), $this->getScopeId(), $full);
        } else {
            return parent::_getConfig($full);
        }
    }

    /**
     * Set correct scope if isSingleStoreMode = true
     *
     * @param Field $cedFieldConfig
     * @param \Magento\Framework\App\Config\ValueInterface $cedDataObject
     * @return void
     */
    protected function _checkSingleStoreMode(
        Field $cedFieldConfig,
        $cedDataObject
    ) {
        $isSingleStoreMode = $this->_storeManager->isSingleStoreMode();
        if (!$isSingleStoreMode)
            return;

        if (!$cedFieldConfig->showInDefault()) {
            $websites = $this->_storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $cedDataObject->setScope('websites');
            $cedDataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $cedDataObject->setScopeCode($singleStoreWebsite->getCode());
            $cedDataObject->setScopeId($singleStoreWebsite->getId());
        }
    }

    /**
     * Get config data value
     * @param string $path
     * @param null $inherit
     * @param null $configData
     * @return \Magento\Framework\Simplexml\Element|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigDataValue($path, &$inherit = null, $configData = null)
    {
        $this->load();
        if ($configData === null) {
            $configData = $this->_configData;
        }

        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->_appConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            $inherit = true;
        }

        return $data;
    }

    /**
     * @param $path
     * @param $scope
     * @param $scopeId
     * @param bool $full
     * @return array
     */
    protected function getConfigByPath($path, $scope, $scopeId, $full = true)
    {
        $is_csgroup = $this->_httpRequest->getParam('is_csgroup');

        switch ($is_csgroup) {
            case 1:
                $groupData = $this->_httpRequest->getPost();
                $gcode =
                    isset($groupData['group_code']) && strlen($groupData['group_code']) > 0 ? $groupData['group_code'] :
                        ($this->_httpRequest->getParam('gcode', false) ? $this->_httpRequest->getParam('gcode') : '');
                if (strlen($gcode) > 0) {
                    $path = $gcode . '/' . $path;
                }
                break;
            case 2 :
                $vendorId = $this->_httpRequest->getParam('vendor_id', 0);
                $vendor = $this->_vendorFactory->create()->load($vendorId);
                if ($vendor && $vendor->getId()) {
                    $path = 'v' . $vendor->getId() . '/' . $path;
                }
        }

        $configDataCollection = $this->_configValueFactory->create()->getCollection()
            ->addScopeFilter($this->getScope(), $this->getScopeId(), $path);

        $config = [];
        foreach ($configDataCollection as $data) {
            if ($full) {
                $config[$data->getPath()] = array(
                    'path' => $data->getPath(),
                    'value' => $data->getValue(),
                    'config_id' => $data->getConfigId()
                );
            } else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }
}
