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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Model\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;

class Config extends \Magento\Cms\Model\Wysiwyg\Config
{
    /**
     * @var
     */
    protected $_httpRequest;
    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Variable\Model\Variable\Config $variableConfig
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param array $windowSize
     * @param array $data
     * @param \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider|null $configProvider
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Variable\Model\Variable\Config $variableConfig,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UrlInterface $urlInterface,
        array $windowSize = [],
        array $data = [],
        ?\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider $configProvider = null
    ) {
        $this->httpRequest = $httpRequest;
        $this->_backendUrl = $backendUrl;
        $this->_scopeConfig = $scopeConfig;
        $this->_authorization = $authorization;
        $this->_assetRepo = $assetRepo;
        $this->_windowSize = $windowSize;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->configProvider = $configProvider ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Cms\Model\Wysiwyg\CompositeConfigProvider::class);
        parent::__construct(
            $backendUrl,
            $eventManager,
            $authorization,
            $assetRepo,
            $variableConfig,
            $widgetConfig,
            $scopeConfig,
            $storeManager,
            $filesystem,
            $windowSize,
            $data,
            $configProvider
        );
    }

    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     */
    public function getConfig($data = [])
    {
        $module_name = $this->httpRequest->getModuleName();
        if ($module_name == 'csproduct') {
            $config = new \Magento\Framework\DataObject();

            $config->setData(
                [
                    'enabled' => $this->isEnabled(),
                    'hidden' => $this->isHidden(),
                    'baseStaticUrl' => $this->_assetRepo->getStaticViewFileContext()->getBaseUrl(),
                    'baseStaticDefaultUrl' => str_replace(
                        'index.php/',
                        '',
                        $this->_backendUrl->getBaseUrl()
                    ) . $this->filesystem->getUri(DirectoryList::STATIC_VIEW) . '/',
                    'directives_url' => $this->_urlInterface->getUrl('csproduct/wysiwyg/directive'),
                    'use_container' => false,
                    'add_variables' => false,
                    'add_widgets' => false,
                    'no_display' => false,
                    'add_directives' => true,
                    'width' => '100%',
                    'height' => '500px',
                    'plugins' => [],
                ]
            );

            $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

            if (is_array($data)) {
                $config->addData($data);
            }

            $this->configProvider->processGalleryConfig($config);
            $config->addData(
                [
                        'files_browser_window_width' => $this->_windowSize['width'],
                        'files_browser_window_height' => $this->_windowSize['height'],
                    ]
            );

            if ($config->getData('add_widgets')) {
                $this->configProvider->processWidgetConfig($config);
            }

            if ($config->getData('add_variables')) {
                $this->configProvider->processVariableConfig($config);
            }

            return $this->configProvider->processWysiwygConfig($config);
        } else {
            return parent::getConfig($data);
        }
    }

    /**
     * Return path for skin images placeholder
     *
     * @return string
     */
    public function getSkinImagePlaceholderPath()
    {
        $staticPath = $this->_storeManager->getStore()->getBaseStaticDir();
        $placeholderPath = $this->_assetRepo->createAsset(self::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID)->getPath();
        return $staticPath . '/' . $placeholderPath;
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        $wysiwygState = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return in_array($wysiwygState, [self::WYSIWYG_ENABLED, self::WYSIWYG_HIDDEN]);
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        $status = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $status == self::WYSIWYG_HIDDEN;
    }
}
