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

namespace Ced\VendorsocialLogin\Model\Twitter\Oauth2;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Client
 * @package Ced\VendorsocialLogin\Model\Twitter\Oauth2
 */
class Client extends \Magento\Framework\DataObject
{
    const REDIRECT_URI_ROUTE = 'cedvendorsociallogin/twitter/connect';
    const REQUEST_TOKEN_URI_ROUTE = 'cedvendorsociallogin/twitter/request';
    const OAUTH_URI = 'https://api.twitter.com/oauth';
    const OAUTH2_SERVICE_URI = 'https://api.twitter.com/1.1';
    const XML_PATH_ENABLED = 'ven_social/ced_sociallogin_twitter/enabled';
    const XML_PATH_CLIENT_ID = 'ven_social/ced_sociallogin_twitter/client_id';
    const XML_PATH_CLIENT_SECRET = 'ven_social/ced_sociallogin_twitter/client_secret';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var mixed|null
     */
    protected $clientId = null;

    /**
     * @var mixed|null
     */
    protected $clientSecret = null;

    /**
     * @var string|null
     */
    protected $redirectUri = null;

    /**
     * @var \Zend_Oauth_Consumer|null
     */
    protected $client = null;

    /**
     * @var null
     */
    protected $token = null;

    /**
     * @var SerializerInterface
     */
    protected $_serializer;

    /**
     * Client constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\UrlInterface $url,
        \Magento\Customer\Model\Session $customerSession,
        SerializerInterface $serializerInterface,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_url = $url;
        $this->redirectUri = $this->_url->sessionUrlVar(
            $this->_url->getUrl(self::REDIRECT_URI_ROUTE)
        );
        $this->clientId = $this->_getClientId();
        $this->clientSecret = $this->_getClientSecret();
        $this->_customerSession = $customerSession;
        $this->_serializer = $serializerInterface;
        $this->client = new \Zend_Oauth_Consumer(
            [
                'callbackUrl' => $this->redirectUri,
                'siteUrl' => self::OAUTH_URI,
                'authorizeUrl' => self::OAUTH_URI . '/authenticate',
                'consumerKey' => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );
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
        return $this->_config->getValue($xmlPath);
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
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
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
     * @param \StdClass $token
     * @throws \Magento\Framework\Exception
     */

    public function setAccessToken($token)
    {
        $this->token = $this->_serializer->unserialize($token);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Oauth_Exception
     */

    public function getAccessToken()
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        }
        return $this->_serializer->serialize($this->token);
    }

    /**
     * @param null $code
     * @return \Zend_Oauth_Token_Access
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Oauth_Exception
     */
    protected function fetchAccessToken($code = null)
    {
        if (!($params = $this->getRequest()->getParams())
            ||
            !($requestToken = $this->_customerSession
                ->getTwitterRequestToken())
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access code.')
            );
        }
        if (!($token = $this->client->getAccessToken(
            $params,
            $this->_serializer->unserialize($requestToken)
        )
        )
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve access token.')
            );
        }

        $this->_customerSession->unsTwitterRequestToken();

        return $this->token = $token;
    }

    /**
     * @return string
     */

    public function createAuthUrl()
    {
        return $this->_url->getUrl(self::REQUEST_TOKEN_URI_ROUTE);
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function api($endpoint, $method = 'GET', $params = [])
    {
        if (empty($this->token)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to proceed without an access token.')
            );
        }

        $url = self::OAUTH2_SERVICE_URI . $endpoint;
        $response = $this->_httpRequest($url, strtoupper($method), $params);

        return $response;
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _httpRequest($url, $method = 'GET', $params = [])
    {
        $client = $this->token->getHttpClient(
            [
                'callbackUrl' => $this->redirectUri,
                'siteUrl' => self::OAUTH_URI,
                'consumerKey' => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );

        $client->setUri($url);

        switch ($method) {
            case 'GET':
                $client->setMethod(\Zend_Http_Client::GET);
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setMethod(\Zend_Http_Client::POST);
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setMethod(\Zend_Http_Client::DELETE);
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Required HTTP method is not supported.')
                );
        }

        $response = $client->request();

        $decoded_response = json_decode($response->getBody());
        if ($response->isError()) {
            $status = $response->getStatus();
            if (($status == 400 || $status == 401 || $status == 429)) {
                if (isset($decoded_response->error->message)) {
                    $message = $decoded_response->error->message;
                } else {
                    $message = __('Unspecified OAuth error occurred.');
                }
                throw new \Magento\Framework\Exception\LocalizedException($message);
            } else {
                $message = sprintf(
                    __('HTTP error %d occurred while issuing request.'),
                    $status
                );
                throw new \Magento\Framework\Exception\LocalizedException($message);
            }
        }

        return $decoded_response;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchRequestToken()
    {
        if (!($requestToken = $this->client->getRequestToken())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve request token.')
            );
        }
        $this->_customerSession
            ->setTwitterRequestToken($this->_serializer->serialize($requestToken));
        $this->client->redirect();
    }
}
