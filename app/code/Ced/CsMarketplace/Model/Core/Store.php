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

namespace Ced\CsMarketplace\Model\Core;

/**
 * Class Store
 * @package Ced\CsMarketplace\Model\Core
 */
class Store extends \Magento\Store\Model\Store
{

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Store constructor.
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Store\Model\ResourceModel\Store $resource
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Config\Model\ResourceModel\Config\Data $configDataResource
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\Information $information
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param string $currencyInstalled
     * @param bool $isCustomEntryPoint
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Store\Model\ResourceModel\Store $resource,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\UrlInterface $url,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\Information $information,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        $currencyInstalled,
        $isCustomEntryPoint = false,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $coreFileStorageDatabase,
            $configCacheType,
            $url,
            $request,
            $configDataResource,
            $filesystem,
            $config,
            $storeManager,
            $sidResolver,
            $httpContext,
            $session,
            $currencyFactory,
            $information,
            $currencyInstalled,
            $groupRepository,
            $websiteRepository,
            $resourceCollection,
            $isCustomEntryPoint,
            $data
        );

        $this->vendorFactory = $vendorFactory;
        $this->registry = $registry;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @param string $path
     * @return mixed|null|string
     */
    public function getConfig($path)
    {
        $path = $this->preparePath($path);
        $data = $this->_config->getValue($path, 'store', $this->getCode());
        if (!$data) {
            $data = $this->_config->getValue($path, 'default');
        }
        return $data === false ? null : $data;
    }

    /**
     * @param $path
     * @param null $group
     * @param int $case
     * @return string
     */
    public function preparePath($path, $group = null, $case = 1)
    {
        if (!preg_match('/ced_/i', $path) ||
            preg_match('/' . preg_quote('ced_csgroup/general/activation', '/') . '/i', $path)
        ) {
            return $path;
        }

        if ($group == null) {
            switch ($case) {
                case 1:
                    if ($this->_moduleManager->isEnabled('Ced_CsCommission')) {
                        if ($this->registry->registry('ven_id')) {
                            $vendor = $this->registry->registry('ven_id');
                            if (is_numeric($this->registry->registry('ven_id'))) {
                                $vendor = $this->vendorFactory->create()->load($this->registry->registry('ven_id'));
                            }
                            if ($vendor && is_object($vendor) && $vendor->getId()) {
                                return 'v' . $vendor->getId() . '/' . $path;
                            }
                        }
                    }
                    return $path;
                default:
                    return $path;
            }
        }
        return $path;
    }
}
