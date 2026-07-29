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
 * @category  Ced
 * @package   Ced_VendorsocialLogin
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\VendorsocialLogin\Model\Facebook\Oauth2;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\UrlInterface;

/**
 * Class Client
 * @package Ced\VendorsocialLogin\Model\Facebook\Oauth2
 */
class Client extends \Magento\Framework\DataObject
{
    const REDIRECT_URI_ROUTE = 'cedvendorsociallogin/facebook/connect';

    const XML_PATH_ENABLED = 'ven_social/ced_sociallogin_facebook/enabled';

    const XML_PATH_CLIENT_ID = 'ven_social/ced_sociallogin_facebook/client_id';

    const XML_PATH_CLIENT_SECRET = 'ven_social/ced_sociallogin_facebook/client_secret';

    const OAUTH2_SERVICE_URI = 'https://graph.facebook.com';

    const OAUTH2_AUTH_URI = 'https://graph.facebook.com/oauth/authorize';

    const OAUTH2_TOKEN_URI = 'https://graph.facebook.com/oauth/access_token';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * Url
     *
     * @var UrlInterface
     */
    protected $_url;

    /**
     *
     * @var string
     */
    protected $clientId = null;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var mixed
     */
    protected $clientSecret = null;

    /**
     *
     * @var string
     */
    protected $redirectUri = null;

    /**
     *
     * @var string
     */
    protected $state = null;

    /**
     *
     * @var array
     */
    protected $scope = ['public_profile', 'email'];

    /**
     * @var token
     */
    protected $token;

    /**
     * Client constructor.
     * @param ZendClientFactory $httpClientFactory
     * @param ScopeConfigInterface $config
     * @param UrlInterface $url
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        \Laminas\Uri\Uri $zendUri,
        RequestInterface $request,
        array $data = []
    ) {
        $this->_httpClientFactory = $httpClientFactory;
        $this->_url = $url;
        $this->_request = $request;
        $this->zendUri = $zendUri;
        $this->scopeConfig = $scopeConfig;
        $this->redirectUri = $this->_url->sessionUrlVar(
            $this->_url->getUrl(self::REDIRECT_URI_ROUTE)
        );
        $this->clientId = $this->_getClientId();
        $this->clientSecret = $this->_getClientSecret();
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    /**
     * @return mixed|string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    protected function _getStoreConfig($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath);
    }

    /**
     * @return mixed
     */
    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    /**
     * @return mixed|string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_isEnabled();
    }

    /**
     * @return mixed
     */
    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @return string
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function getAccessToken()
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        }
        return json_encode($this->token);
    }

    /**
     * @param null $code
     * @return string
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function fetchAccessToken($code = null)
    {
        if (empty($this->_request->getParam('code'))) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access code.')
            );
        }

        $response = $this->_httpRequest(
            self::OAUTH2_TOKEN_URI,
            'POST',
            [

                'code' => $this->_request->getParam('code'),

                'redirect_uri' => $this->getRedirectUri(),

                'client_id' => $this->getClientId(),

                'client_secret' => $this->getClientSecret(),

                'grant_type' => 'authorization_code'

            ]
        );

        $this->setAccessToken($response);

        return $this->getAccessToken();
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function _httpRequest($url, $method = 'GET', $params = [])
    {
        $client = $this->_httpClientFactory->create();

        $client->setUri($url);

        switch ($method) {

            case 'GET':
                $client->setParameterGet($params);

                break;

            case 'POST':
                $client->setParameterPost($params);

                break;

            case 'DELETE':
                $client->setParameterGet($params);

                break;

            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Required HTTP method is not supported.')
                );

        }

        $response = $client->request($method);

        $decodedResponse = json_decode($response->getBody());

        if (empty($decodedResponse)) {
            $parsed_response = [];
            $this->zendUri->setQuery($response->getBody());
            $parsed_response = $this->zendUri->getQueryAsArray();
            //parse_str($response->getBody(), $parsed_response);

            $decodedResponse = json_decode(json_encode($parsed_response));
        }

        if ($response->isError()) {
            $status = $response->getStatus();

            if (($status == 400 || $status == 401)) {
                if (isset($decodedResponse->error->message)) {
                    $message = $decodedResponse->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }

                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
                    $status
                );

                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        }

        return $decodedResponse;
    }

    /**
     * @return string
     */

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param \StdClass $token
     */
    public function setAccessToken(\StdClass $token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */

    public function createAuthUrl()
    {
        $url =

            self::OAUTH2_AUTH_URI . '?' .

            http_build_query(
                [

                    'client_id' => $this->getClientId(),

                    'redirect_uri' => $this->getRedirectUri(),

                    'state' => $this->getState(),

                    'scope' => implode(',', $this->getScope())

                ]
            );

        return $url;
    }

    /**
     * @return string
     */

    public function getState()
    {
        return $this->state;
    }

    /**
     * @param $state
     */

    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return array
     */

    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */

    public function api($endpoint, $method = 'GET', $params = [])
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        }

        $url = self::OAUTH2_SERVICE_URI . $endpoint;

        $method = strtoupper($method);

        $params = array_merge([
            'access_token' => $this->token->access_token
        ], $params);

        $response = $this->_httpRequest($url, $method, $params);

        return $response;
    }
}
