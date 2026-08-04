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

namespace Ced\CsMarketplace\Controller\Adminhtml\Main;

use Ced\CsMarketplace\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class License
 * @package Ced\CsMarketplace\Controller\Adminhtml\Main
 */
class License extends \Magento\Backend\App\Action
{
    /**
     * @var mixed
     */
    protected $_curl;

    /**
     * @var mixed
     */
    protected $logger;

    const LICENSE_ACTIVATION_URL_PATH = 'system/license/activate_url';

    /**
     * @var null
     */
    protected $_licenseActivateUrl = null;

    /**
     * @var \Ced\CsMarketplace\Helper\Feed|null
     */
    protected $_feedHelper = null;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;

    /**
     * License constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\CsMarketplace\Helper\Feed $feedHelper
     * @param Data $csmarketplaceHelper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Url\DecoderInterface $decoder
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Helper\Feed $feedHelper,
        Data $csmarketplaceHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Url\DecoderInterface $decoder,
        ?\Psr\Log\LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->_curl = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_feedHelper = $feedHelper;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->decoder = $decoder;
        $this->logger = $logger ?? $this->_objectManager->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $postData = $this->getRequest()->getParams();
        unset($postData['key']);
        unset($postData['form_key']);
        unset($postData['isAjax']);

        $json = ['success' => 0, 'message' => __('There is an Error Occurred.')];
        if ($postData) {
            foreach ($postData as $moduleName => $licensekey) {
                if (preg_match('/ced_/i', $moduleName)) {
                    if (strlen($licensekey) == 0) {
                        $json = ['success' => 1, 'message' => ''];
                        $resultJson->setData($json);
                        return $resultJson;
                    }
                    unset($postData[$moduleName]);
                    $postData['module_name'] = $moduleName;
                    $allModules = $this->_feedHelper->getAllModules();

                    $postData['module_version'] = isset($allModules[$moduleName]['release_version']) ?
                        $allModules[$moduleName]['release_version'] : '';
                    $postData['module_license'] = $licensekey;
                    break;
                }
            }

            $response = $this->validateAndActivateLicense($postData);

            if ($response && isset($response['hash']) && isset($response['level'])) {

                $json = ['success' => 0, 'message' => __('There is an Error Occurred.')];
                $valid = $response['hash'];
                try {

                    for ($i = 1; $i <= $response['level']; $i++) {
                        $valid = $this->decoder->decode($valid);
                    }
                    $valid = json_decode($valid, true);

                    if (is_array($valid) &&
                        isset($valid['domain']) &&
                        isset($valid['module_name']) &&
                        isset($valid['license']) &&
                        $valid['module_name'] == $postData['module_name'] &&
                        $valid['license'] == $postData['module_license']
                    ) {
                        $path = \Ced\CsMarketplace\Block\Extensions::HASH_PATH_PREFIX .
                            strtolower($postData['module_name']) . '_hash';
                        $this->_feedHelper->setDefaultStoreConfig($path, $response['hash'], 0);
                        $path = \Ced\CsMarketplace\Block\Extensions::HASH_PATH_PREFIX .
                            strtolower($postData['module_name']) . '_level';
                        $this->_feedHelper->setDefaultStoreConfig($path, $response['level'], 0);
                        $json['success'] = 1;
                        $json['message'] = __('Module Activated successfully.');

                        $this->_feedHelper->checkLicense();

                    } else {
                        $json['success'] = 0;
                        $json['message'] = isset($response['error']['code']) && isset($response['error']['msg']) ?
                            'Error (' . $response['error']['code'] . '): ' . $response['error']['msg'] :
                            __('Invalid License Key.');
                    }
                } catch (\Exception $e) {
                    $json['success'] = 0;
                    $json['message'] = __('Invalid License');
                    $this->logger->critical($e);
                }
            }
        }
        $resultJson->setData($json);
        return $resultJson;

    }

    /**
     * @param array $urlParams
     * @return bool|mixed
     * @throws \Exception
     */
    private function validateAndActivateLicense($urlParams = [])
    {
        $body = '';
        if (isset($urlParams['form_key'])) unset($urlParams['form_key']);
        $urlParams = array_merge($this->_feedHelper->getEnvironmentInformation(), $urlParams);

        if (is_array($urlParams) && count($urlParams) > 0) {

            if (isset($urlParams['installed_extensions_by_cedcommerce']))
                unset($urlParams['installed_extensions_by_cedcommerce']);
            $body = $this->_feedHelper->addParams('', $urlParams);
            $body = trim($body, '?');

        }

        try {
            $url = $this->getLicenseActivateUrl();
            $params = [
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => false
            ];
            $this->_curl->post($url, $params);
            $result = $this->_curl->getBody();
            $result = json_decode($result, true);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getLicenseActivateUrl()
    {
        if ($this->_licenseActivateUrl === null) {
            $this->_licenseActivateUrl =
                ($this->_feedHelper->getStoreConfig(\Ced\CsMarketplace\Block\Extensions::LICENSE_USE_HTTPS_PATH) ?
                    'https://' : 'https://')
                . $this->_feedHelper->getStoreConfig(self::LICENSE_ACTIVATION_URL_PATH);
        }
        return $this->_licenseActivateUrl;
    }
}
