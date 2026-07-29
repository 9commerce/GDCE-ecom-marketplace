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

namespace Ced\CsMarketplace\Plugin;

/**
 * Class SetVendorPanel
 * @package Ced\CsMarketplace\Plugin
 */
class SetVendorPanel
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * SetVendorPanel constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registry;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfigurationDesignTheme($subject, $result)
    {
        if ($this->_coreRegistry->registry('vendorPanel')) {
            $result = $this->_scopeConfig->getValue('ced_csmarketplace/vendor/theme');
        }
        return $result;
    }
}
