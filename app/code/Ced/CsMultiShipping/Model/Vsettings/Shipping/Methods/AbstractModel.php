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

namespace Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods;

use Magento\Framework\Api\AttributeValueFactory;

class AbstractModel extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    const SHIPPING_SECTION = 'shipping';

    /**
     * @var string
     */
    protected $_code = '';

    /**
     * @var array
     */
    protected $_fields = [];

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * AbstractModel constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get current store
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId) {
            return $this->csmarketplaceHelper->getStore($storeId);
        } else {
            return $this->csmarketplaceHelper->getStore();
        }
    }

    /**
     * Get current store
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get the code
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get the code separator
     * @return string
     */
    public function getCodeSeparator()
    {
        return $this->_codeSeparator;
    }

    /**
     *  Retreive input fields
     * @return array
     */
    public function getFields()
    {
        $this->_fields = [];
        $this->_fields['active'] = [
            'type' => 'select',
            'values' => [
                ['label' => __('Yes'), 'value' => 1],
                ['label' => __('No'), 'value' => 0]
              ]
            ];
        return $this->_fields;
    }

    /**
     * Retreive labels
     *
     * @param string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'active':
                return __('Active');
            default:
                return __($key);
        }
    }

    /**
     * @param $methodData
     * @return bool
     */
    public function validateSpecificMethod($methodData)
    {
        if (count($methodData) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
