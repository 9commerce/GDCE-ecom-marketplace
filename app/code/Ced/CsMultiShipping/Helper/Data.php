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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Helper;

use Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vsettings;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory
     */
    protected $_vsettingsCollectionFactory;

    /**
     * @var \Ced\CsMultiShipping\Model\Source\Shipping\Methods
     */
    protected $methods;

    /**
     * @var \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address
     */
    protected $address;

    /**
     * Data constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettings
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory $vsettingsCollectionFactory
     * @param \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods
     * @param \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address $address
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettings,
        \Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory $vsettingsCollectionFactory,
        \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods,
        \Ced\CsMultiShipping\Model\Vsettings\Shipping\Address $address,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vsettings = $vsettings;
        $this->_vsettingsCollectionFactory = $vsettingsCollectionFactory;
        $this->methods = $methods;
        $this->address = $address;
        parent::__construct($context);
    }

    /**
     * @param int $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled($storeId = 0)
    {
        if ($storeId == 0) {
            $storeId = $this->csmarketplaceHelper->getStore()->getId();
        }
        return $this->csmarketplaceHelper->getStoreConfig('ced_csmultishipping/general/activation', $storeId);
    }

    /**
     * @param string $key
     * @param int $vendorId
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigValue($key = '', $vendorId = 0)
    {
        $value = false;
        if (strlen($key) > 0 && $vendorId) {
            $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
            $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
            $vsettings = $this->vsettings->create()
                ->loadByField([$key_tmp, $vendor_id_tmp], [$key, (int)$vendorId]);
            if ($vsettings && $vsettings->getSettingId()) {
                $value = $vsettings->getValue();
            }
        }
        return $value;
    }

    /**
     * @param int $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getActiveVendorMethods($vendorId = 0)
    {
        $methods = $this->methods->getMethods();
        $VendorMethods = [];
        if (count($methods) > 0) {
            $vendorShippingConfig = $this->getShippingConfig($vendorId);
            foreach ($methods as $code => $method) {
                $objectm = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $objectm->create($method['model']);
                $key = strtolower(AbstractModel::SHIPPING_SECTION . '/' . $code . '/active');
                if (isset($vendorShippingConfig[$key]['value']) && $vendorShippingConfig[$key]['value']) {
                    $fields = $model->getFields();
                    foreach ($fields as $id => $field) {
                        $key = strtolower(AbstractModel::SHIPPING_SECTION . '/' . $code . '/' . $id);
                        if (isset($vendorShippingConfig[$key])) {
                            $VendorMethods[$code][$id] = $vendorShippingConfig[$key]['value'];
                        }
                    }
                }
            }
            return $VendorMethods;
        } else {
            return $VendorMethods;
        }
    }

    /**
     * @param int $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorMethods($vendorId = 0)
    {
        $methods = $this->methods->getMethods();
        $VendorMethods = [];
        if (count($methods) > 0) {
            $vendorShippingConfig = $this->getShippingConfig($vendorId);
            foreach ($methods as $code => $method) {
                $objectM = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $objectM->get($method['model']);
                $fields = $model->getFields();
                if (count($fields) > 0) {
                    foreach ($fields as $id => $field) {
                        $key = strtolower(AbstractModel::SHIPPING_SECTION . '/' . $code . '/' . $id);
                        if (isset($vendorShippingConfig[$key])) {
                            $VendorMethods[$code][$id] = $vendorShippingConfig[$key]['value'];
                        }
                    }
                }
            }
            return $VendorMethods;
        } else {
            return $VendorMethods;
        }
    }

    /**
     * @param int $vendorId
     * @return array
     */
    public function getVendorAddress($vendorId = 0)
    {
        $VendorAddress = [];
        $model = $this->address;
        $vendorShippingConfig = $this->getShippingConfig($vendorId);
        $fields = $model->getFields();
        if (count($fields) > 0) {
            foreach ($fields as $id => $field) {
                $key = strtolower(AbstractModel::SHIPPING_SECTION . '/address/' . $id);
                if (isset($vendorShippingConfig[$key]) && strlen($vendorShippingConfig[$key]['value']) > 0) {
                    $VendorAddress[$id] = $vendorShippingConfig[$key]['value'];
                }
            }
        }
        return $VendorAddress;
    }

    /**
     * @param int $vendorId
     * @return array
     */
    public function getShippingConfig($vendorId = 0)
    {
        $values = [];
        if ($vendorId) {
            $group = $this->csmarketplaceHelper->getTableKey('group');
            $vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');
            $vsettings = $this->_vsettingsCollectionFactory->create()
                ->addFieldToFilter($group, ['eq' => AbstractModel::SHIPPING_SECTION])
                ->addFieldToFilter($vendor_id, ['eq' => $vendorId]);
            if ($vsettings && count($vsettings->getData()) > 0) {
                foreach ($vsettings->getData() as $index => $value) {
                    $values[$value['key']] = $value;
                }
            }
        }
        return $values;
    }

    /**
     * @param $section
     * @param $groups
     * @param $vendor_id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveShippingData($section, $groups, $vendor_id)
    {
        foreach ($groups as $code => $values) {
            if (is_array($values) && count($values) > 0) {
                foreach ($values as $name => $value) {
//                    if(($name !== "region" && $name !== "region_id" && trim($value) === '') || $value === null){
//                        throw new \Magento\Framework\Exception\LocalizedException(__('Please fill all the required fields.'));
//                    }
//                    if(isset($values['region_id']) === true && $values['region_id'] === null && $values['region'] === null){
//                        throw new \Magento\Framework\Exception\LocalizedException(__('Please fill all the required fields.'));
//                    }
                    $serialized = 0;
                    $key = strtolower($section . '/' . $code . '/' . $name);
                    if (is_array($value)) {
                        $value = implode(",", $value);
                    }
                    $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
                    $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
                    $setting = $this->vsettings->create()
                        ->loadByField([$key_tmp, $vendor_id_tmp], [$key, $vendor_id]);
                    if ($setting && $setting->getId()) {
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    } else {
                        $setting = $this->vsettings->create();
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    }
                }
            }
        }
    }

    /**
     * @param array $vendorAddress
     * @return bool
     */
    public function validateAddress($vendorAddress = [])
    {
        $flag = true;
        if (!isset($vendorAddress['country_id']) ||
            !isset($vendorAddress['city']) || !isset($vendorAddress['postcode'])) {
            return false;
        }
        if (!isset($vendorAddress['region_id']) && !isset($vendorAddress['region'])) {
            return false;
        }
        if (isset($vendorAddress['country_id']) && !$vendorAddress['country_id']) {
            return false;
        }
        if (isset($vendorAddress['region_id']) && !$vendorAddress['region_id']) {
            return false;
        }
        if (isset($vendorAddress['region']) && !$vendorAddress['region']) {
            return false;
        }
        if (!isset($vendorAddress['city']) && !$vendorAddress['city']) {
            return false;
        }
        if (!isset($vendorAddress['postcode']) && !$vendorAddress['postcode']) {
            return false;
        }
        return $flag;
    }

    /**
     * @param $activeMethods
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateSpecificMethods($activeMethods)
    {
        if (count($activeMethods) > 0) {
            $methods = $this->methods->getMethods();
            foreach ($activeMethods as $method => $methoddata) {
                if (isset($methods[$method]['model'])) {
                    $object = \Magento\Framework\App\ObjectManager::getInstance();
                    $model = $object->get($methods[$method]['model'])->validateSpecificMethod($activeMethods[$method]);
                    if (!$model) {
                        return false;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
}
