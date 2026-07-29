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

namespace Ced\CsMarketplace\Model\Vendor\Payment\Methods;

use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Class AbstractModel
 * @package Ced\CsMarketplace\Model\Vendor\Payment\Methods
 */
class AbstractModel extends \Magento\Payment\Model\Method\AbstractMethod
{

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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AbstractModel constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current store
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        $storeId = (int)$this->request->getParam('store', 0);
        if ($storeId) {
            return $this->storeManager->getStore($storeId);
        } else {
            return $this->storeManager->getStore(null);
        }
    }

    /**
     * Get the code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get the code separator
     *
     * @return string
     */
    public function getCodeSeparator()
    {
        return $this->_codeSeparator;
    }

    /**
     *  Retrieve input fields
     *
     * @return array
     */
    public function getFields()
    {
        $this->_fields = [];
        $this->_fields['active'] = [
            'type' => 'select',
            'values' => ['0' => __('No'), '1' => __('Yes')]
        ];
        return $this->_fields;
    }

    /**
     * Retrieve labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'active' :
                return __('Active');
            default :
                return __($key);
        }
    }
}
