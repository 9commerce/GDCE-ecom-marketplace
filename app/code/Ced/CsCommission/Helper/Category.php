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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Category extends AbstractHelper
{
    const OPTION_CATEGORY_PREFIX = '';
    const OPTION_CATEGORY_PREFIX_SEPARATOR = '';
    const TYPE_DEFAULT = 'default';
    const DEFAULT_TYPE_ID = 0;
    const DEFAULT_VENDOR_ID = 0;
    const XML_CONFIGURATION_CATEGORY_WISE_COMMISSION = 'ced_vpayments/general/commission_cw';
    /**
     * @var \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category constructor.
     * @param Context $context
     * @param \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Ced\CsCommission\Model\ResourceModel\Commission\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param $value
     * @return false|string
     */
    public function getSerializedOptions($cvalue)
    {
        $uniqueValues = [];
        if (is_array($cvalue)) {
            $cnt = 0;
            foreach ($cvalue as $key => $cval) {
                if (!is_array($cval)) {
                    continue;
                }
                if (isset($cval['method']) && !in_array($cval['method'], ['fixed', 'percentage'])) {
                    $cval['method'] = 'fixed';
                }
                switch ($cval['method']) {
                    case "fixed":
                        $cval['fee'] = round($cval['fee'], 2);
                        break;
                    case "percentage":
                        $cval['fee'] = min((int)$cval['fee'], 100);
                        break;
                }
                if (isset($cval['priority']) && !is_numeric($cval['priority'])) {
                    $lengthPriority = strlen($cval['priority']);
                    if ($lengthPriority > 0) {
                        $cval['priority'] = (int)$cval['priority'];
                    } else {
                        $cval['priority'] = $cnt;
                    }
                }

                if (!isset($uniqueValues[$this->getCodeValue($cval['category'])])) {
                    $uniqueValues[$this->getCodeValue($cval['category'])] = $cval;
                } elseif (isset($uniqueValues[$this->getCodeValue($cval['category'])]) &&
                    isset($uniqueValues[$this->getCodeValue($cval['category'])]['priority']) &&
                    isset($cval['priority'])
                    && (int)$cval['priority'] < (int)$uniqueValues[$this
                        ->getCodeValue($cval['category'])]['priority']) {
                    $uniqueValues[$this->getCodeValue($cval['category'])] = $cval;
                }
                $cnt++;
            }
        }
        if ($uniqueValues != '') {
            return json_encode($uniqueValues);
        } else {
            return '';
        }
    }

    /**
     * @param string $category
     * @return string
     */
    public function getCodeValue($category = 'all')
    {
        return self::OPTION_CATEGORY_PREFIX . self::OPTION_CATEGORY_PREFIX_SEPARATOR . $category;
    }

    /**
     * @param null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOptions($storeId = null)
    {
        $rawOptions = $this->getUnserializedOptions($storeId);
        return $rawOptions;
    }

    /**
     * @param int $vendorId
     * @param int $storeId
     * @return array
     */
    public function getUnserializedOptions($vendorId = 0, $storeId = 0)
    {
        $data = [];
        $categoryCommissionIds = $this->scopeConfig->getValue(
            self::XML_CONFIGURATION_CATEGORY_WISE_COMMISSION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($vendorId) {
            $vcategoryCommissionIds = $this->scopeConfig->getValue(
                'v' . $vendorId . '/' . self::XML_CONFIGURATION_CATEGORY_WISE_COMMISSION
            );
            if ($vcategoryCommissionIds && $vcategoryCommissionIds !='') {
                $categoryCommissionIds = $vcategoryCommissionIds;
            }
        }
        if ($categoryCommissionIds && $categoryCommissionIds !='') {
            $ccommissionIds = explode(',', $categoryCommissionIds);
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('id', ['in'=>$ccommissionIds]);
            $collection->setOrder('priority', 'ASC');
            if ($collection->count() > 0) {
                $collection = $collection->toArray();
                foreach ($collection['items'] as $commission) {
                    $data[$commission['category']] = $commission;
                }
            }
        }
        return $data;
    }

    /**
     * @param string $type
     * @param int $typeId
     * @param int $vendorId
     * @param $postData
     * @return mixed
     */
    public function setCategoryWiseCommissionConfig(
        $postData,
        $type = self::TYPE_DEFAULT,
        $typeId = self::DEFAULT_TYPE_ID,
        $vendorId = self::DEFAULT_VENDOR_ID
    ) {
        if (isset($postData['groups']) && isset($postData['groups']['vpayments']) &&
            isset($postData['groups']['vpayments']['fields'])) {
            $commissionIds = '';
            $commissionCollection = $this->collectionFactory->create();
            $ids = $commissionCollection->addFieldToFilter('type', $type)
                ->addFieldToFilter('type_id', $typeId)
                ->addFieldToFilter('vendor', $vendorId)
                ->getColumnValues('id');
            if (!empty($ids)) {
                $commissionIds = implode(',', $ids);
            }

            $postData['groups']['vpayments']['fields']['commission_cw']['value'] = $commissionIds;
        }
        return $postData;
    }
}
