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

namespace Ced\CsMarketplace\Model\System\Config\Source;

/**
 * Class Rate
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class Rate implements \Magento\Framework\Data\OptionSourceInterface
{
    const XML_PATH_CED_CSMARKETPLACE_VENDOR_RATES = 'ced_csmarketplace/vendor/rates';

    /**
     * @var
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManger;

    /**
     * Rate constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManger = $storeManager;
        $this->_context = $context;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        if (is_array($this->getRates())) {
            $rates = array_keys($this->getRates());
            $options = [];
            foreach ($rates as $rate) {
                $rate = strtolower(trim($rate));
                $options[] = ['value' => $rate, 'label' => ucfirst($rate)];
            }
        }
        return $options;
    }

    /**
     * Retrieve rates data form config.xml
     *
     * @return array
     */
    public function getRates()
    {
        $rates = $this->_context->getScopeConfig()->getValue(self::XML_PATH_CED_CSMARKETPLACE_VENDOR_RATES);
        return json_decode(json_encode($rates), true);
    }
}
