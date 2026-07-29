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

namespace Ced\CsMarketplace\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;


/**
 * Class Feed
 * @package Ced\CsMarketplace\Model
 */
class Feed extends \Magento\AdminNotification\Model\Feed
{

    const XML_USE_HTTPS_PATH = 'system/adminnotification/use_https';
    const XML_FEED_URL_PATH = 'system/csmarketplace/feed_url';
    const XML_FREQUENCY_PATH = 'system/csmarketplace/frequency';
    const XML_LAST_UPDATE_PATH = 'system/csmarketplace/last_update';

    const XML_FEED_TYPES = 'cedcore/feeds_group/feeds';
    const XML_PATH_INSTALLATED_MODULES = 'module';

    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var \Ced\CsMarketplace\Helper\Feed
     */
    protected $marketplaceFeedHelper;

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\Module\Declaration\Converter\Dom
     */
    protected $dom;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    protected $loader;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $moduleRegistry;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $parser;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $filesystemDriver;

    /**
     * @var \Magento\AdminNotification\Model\InboxFactory
     */
    protected $_inboxFactory;

    /**
     * @var RequestInterface
     */
    protected $http;

    /**
     * @var
     */
    private $_serializer;

    /**
     * Feed constructor.
     * @param \Ced\CsMarketplace\Helper\Feed $marketplaceFeedHelper
     * @param \Laminas\Uri\Http $http
     * @param \Magento\Framework\Module\ModuleListInterface $moduleLoader
     * @param \Magento\Framework\Module\Declaration\Converter\Dom $dom
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry
     * @param \Magento\Framework\Xml\Parser $parser
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param \Magento\Framework\Module\ModuleList\Loader $loader
     * @param RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\AdminNotification\Model\InboxFactory $inboxFactory
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Feed $marketplaceFeedHelper,
        \Laminas\Uri\Http $http,
        \Magento\Framework\Module\ModuleListInterface $moduleLoader,
        \Magento\Framework\Module\Declaration\Converter\Dom $dom,
        \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \Magento\Framework\Module\ModuleList\Loader $loader,
        RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\AdminNotification\Model\InboxFactory $inboxFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\UrlInterface $urlBuilder,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->http = $http;
        $this->moduleList = $moduleLoader;
        $this->dom = $dom;
        $this->loader = $loader;
        $this->moduleRegistry = $moduleRegistry;
        $this->parser = $parser;
        $this->filesystemDriver = $filesystemDriver;
        $this->_inboxFactory = $inboxFactory;
        $this->request = $request;
        $this->marketplaceFeedHelper = $marketplaceFeedHelper;
        $this->_serializer = $serializer;

        parent::__construct($context, $registry, $backendConfig, $inboxFactory, $curlFactory, $deploymentConfig,
            $productMetadata, $urlBuilder);
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            if (trim($data) != '') {
                $xml = new \SimpleXMLElement((string)$data);
            } else {
                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
            }
        } catch (\Exception $e) {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
        }

        return $xml;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @param array $urlParams
     * @return bool|\SimpleXMLElement
     */
    public function getFeedData($urlParams = [])
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout' => 10
            ]
        );
        $body = '';
        if (is_array($urlParams) && count($urlParams) > 0) {
            $body = $this->addParams('', $urlParams);
            $body = trim($body, '?');
        }

        try {
            $curl->write(\Laminas\Http\Request::METHOD_POST, $this->getFeedUrl(), '1.1', array(), $body);
            $data = $curl->read();

            if ($data === false) {
                return false;
            }
            //uncomment this
            $data = preg_split('/^\r?$/m', $data, 2);

            $data = trim($data[1]);

            if (trim($data) == '') {
                return false;
            }

            if ($curl->getInfo() || true) {
                $xml = new \SimpleXMLElement((string)$data);
            } else {
                return false;
            }
            $curl->close();
        } catch (\Exception $e) {
            return false;
        }

        return $xml;
    }

    /**
     * Add params into url string
     *
     * @param  string $url (default '')
     * @param  array $params (default array())
     * @param  boolean $urlencode (default true)
     * @return string | array
     */
    public function addParams($url = '', $params = [], $urlencode = true)
    {
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $parse = $this->http->parse($url);
                if ($parse) {
                    if (!$urlencode) {
                        $url .= '&' . $key . '=' . $value;
                    } else {
                        $url .= '&' . $key . '=' . $this->prepareParams($value);
                    }
                } else {
                    if (!$urlencode) {
                        $url .= '?' . $key . '=' . $value;
                    } else {
                        $url .= '?' . $key . '=' . $this->prepareParams($value);
                    }
                }
            }
        }
        return $url;
    }

    /**
     * Url encode the parameters
     *
     * @param  string | array
     * @return string | array | boolean
     */
    public function prepareParams($data)
    {
        if (!is_array($data) && strlen($data)) {
            return urlencode($data);
        }
        if ($data && is_array($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                $data[$key] = urlencode($value);
            }
            return $data;
        }
        return false;
    }

    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl()
    {
        if ($this->_feedUrl === null) {
            $this->_feedUrl = ($this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    /**
     * @param $module
     * @return bool|string
     */
    public function getReleaseVersion($module)
    {
        $modulePath = $this->moduleRegistry->getPath(self::XML_PATH_INSTALLATED_MODULES, $module);
        $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, "$modulePath/etc/module.xml");
        $source = new \Magento\Framework\Simplexml\Config($filePath);
        if ($source->getNode(self::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version) {
            return $source->getNode(self::XML_PATH_INSTALLATED_MODULES)->attributes()->release_version->__toString();
        }
        return false;
    }

    /**
     * get feed from cedcommerce
     *
     */
    public function invoke()
    {
        $this->checkUpdate();
    }

    /**
     * Check feed for modification
     *
     * @return Feed
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     * @throws \Exception
     */
    public function checkUpdate()
    {
        $params = $this->request->getParams();
        if (!isset($params['testdev'])) {
            if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
                return $this;
            }
        }

        $feedData = $feed = [];
        $feedXml = $this->getFeedData($this->marketplaceFeedHelper->getEnvironmentInformation());
        //$allowedFeedType = explode(',', $this->_backendConfig->getValue(self::XML_FEED_TYPES));

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            $installedModules = $this->marketplaceFeedHelper->getCedCommerceExtensions();
            foreach ($feedXml->channel->item as $item) {
                if (!isset($installedModules[(string)$item->module])) {
                    continue;
                }

                if ($this->marketplaceFeedHelper->isAllowedFeedType($item)) {
                    if (strlen(trim($item->module)) > 0) {
                        if (isset($feedData[trim((string)$item->module)]) &&
                            isset($feedData[trim((string)$item->module)]['release_version']) &&
                            strlen((string)$item->release_version) > 0 &&
                            version_compare($feedData[trim((string)$item->module)]['release_version'],
                                trim((string)$item->release_version), '>') === true
                        ) {
                            continue;
                        }
                        $feedData[trim((string)$item->module)] = [
                            'severity' => (int)$item->severity,
                            'date_added' => $this->getDate((string)$item->pubDate),
                            'title' => (string)$item->title,
                            'description' => (string)$item->description,
                            'url' => (string)$item->link,
                            'module' => (string)$item->module,
                            'release_version' => (string)$item->release_version,
                            'update_type' => (string)$item->update_type,
                        ];
                        if (strlen((string)$item->warning) > 0) {
                            $feedData[trim((string)$item->module)]['warning'] = (string)$item->warning;
                        }

                        if (strlen((string)$item->product_url) > 0) {
                            $feedData[trim((string)$item->module)]['url'] = (string)$item->product_url;
                        }

                    }

                    $feed[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => $this->getDate((string)$item->pubDate),
                        'title' => (string)$item->title,
                        'description' => (string)$item->description,
                        'url' => (string)$item->link
                    ];
                }
            }

            if ($feed) {
                $this->_inboxFactory->create()->parse(array_reverse($feed));
            }
            if ($feedData) {
                $value = $this->_serializer->serialize($feedData);
                $this->_cacheManager->save($value, 'all_extensions_by_cedcommerce');
            }

        }
        $this->setLastUpdate();

        return $this;
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency()
    {
        return $this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {

        return $this->_cacheManager->load('ced_notifications_lastcheck');
    }

    /**
     * Retrieve DB date from RSS date
     *
     * @param  string $rssDate
     * @return string YYYY-MM-DD YY:HH:SS
     */
    public function getDate($rssDate)
    {
        return gmdate('Y-m-d H:i:s', strtotime($rssDate));
    }

    /**
     * Set last update time (now)
     *
     * @return Feed
     */
    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'ced_notifications_lastcheck');
        return $this;
    }
}
