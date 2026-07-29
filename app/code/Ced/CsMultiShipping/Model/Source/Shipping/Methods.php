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

namespace Ced\CsMultiShipping\Model\Source\Shipping;

class Methods extends \Ced\CsMarketplace\Model\System\Config\Source\AbstractBlock
{
    const XML_PATH_CED_CSMULTISHIPPING_SHIPPING_METHODS = 'ced_csmultishipping/shipping/methods';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * Methods constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve rates data form config.xml
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMethods()
    {
        $rates = $this->scopeConfig->getValue(
            self::XML_PATH_CED_CSMULTISHIPPING_SHIPPING_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->csmarketplaceHelper->getStore()
        );

        $allowedmethods = [];
        if (is_array($rates) && count($rates) > 0) {
            foreach ($rates as $code => $method) {
                if ($this->csmarketplaceHelper->getStoreConfig(
                    $method['config_path'],
                    $this->csmarketplaceHelper->getStore()->getId()
                )) {
                    $allowedmethods[$code] = $rates[$code];
                }
            }
        }
        return $allowedmethods;
    }

    /**
     * Retrieve Option values array
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function toOptionArray()
    {
        $methods = array_keys(self::getMethods());
        $options = [];
        foreach ($methods as $method) {
            $method = strtolower(trim($method));
            $options[] = ['value' => $method, 'label' => __(ucfirst($method))];
        }
        return $options;
    }
}
