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

namespace Ced\CsMarketplace\Block\Adminhtml;


use Ced\CsMarketplace\Block\Extensions;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Base64Json;

/**
 * Class Popup
 * @package Ced\CsMarketplace\Block\Adminhtml
 */
class Popup extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @var \Ced\CsMarketplace\Helper\Feed
     */
    protected $feedHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var
     */
    protected $_serializer;

    /**
     * Popup constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Ced\CsMarketplace\Helper\Feed $feedHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param Base64Json|null $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Ced\CsMarketplace\Helper\Feed $feedHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        ?Base64Json $serializer = null,
        array $data = []
    ) {
        $this->feedHelper = $feedHelper;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Base64Json::class);
        parent::__construct($context, $data);
    }

    /**
     * @return $this|string
     * @throws \Exception
     */
    public function getModules()
    {
        $modules = $this->feedHelper->getCedCommerceExtensions();
        $helper = $this->csmarketplaceHelper;
        $args = '';
        foreach ($modules as $moduleName => $releaseVersion) {
            $m = strtolower($moduleName);
            if (!preg_match('/ced/i', $m))
                return $this;

            $level = $helper->getStoreConfig(Extensions::HASH_PATH_PREFIX . $m . '_level');
            $h = $helper->getStoreConfig(Extensions::HASH_PATH_PREFIX . $m . '_hash');
            for ($i = 1; $i <= (int)$level; $i++) {
                $h = $this->_serializer->unserialize($h);
            }
            
            if (is_array($h) && isset($h['domain']) && isset($h['module_name']) && isset($h['license']) &&
                $h['module_name'] == $m &&
                $h['license'] == $helper->getStoreConfig(Extensions::HASH_PATH_PREFIX . $m)
            ) {
            } else {
                $args .= $m . ',';
            }
        }

        $args = trim($args, ',');
        return $args;
    }
}
