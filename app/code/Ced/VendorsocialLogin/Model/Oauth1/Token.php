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

namespace Ced\VendorsocialLogin\Model\Oauth1;

/**
 * OAuth 1.0a request/access token, replacing Zend_Oauth_Token_(Request|Access).
 */
class Token implements \JsonSerializable
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $tokenSecret;

    /**
     * Extra params returned alongside the token (e.g. user_id, screen_name).
     *
     * @var array
     */
    private $params;

    /**
     * @param string $token
     * @param string $tokenSecret
     * @param array $params
     */
    public function __construct($token, $tokenSecret, array $params = [])
    {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'token' => $this->token,
            'tokenSecret' => $this->tokenSecret,
            'params' => $this->params,
        ];
    }

    /**
     * Rebuild a Token from the array produced by jsonSerialize(), e.g. after it has
     * round-tripped through session storage via SerializerInterface::(un)serialize().
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        return new self($data['token'] ?? '', $data['tokenSecret'] ?? '', $data['params'] ?? []);
    }

    /**
     * Build an HTTP client that automatically OAuth1-signs every request made with it,
     * matching the interface previously provided by Zend_Oauth_Token_Access::getHttpClient().
     *
     * @param array $oauthOptions Must contain consumerKey and consumerSecret
     * @return SignedClient
     */
    public function getHttpClient(array $oauthOptions)
    {
        $client = new SignedClient();
        $client->setOauthCredentials(
            $oauthOptions['consumerKey'] ?? '',
            $oauthOptions['consumerSecret'] ?? '',
            $this->token,
            $this->tokenSecret
        );

        return $client;
    }
}
