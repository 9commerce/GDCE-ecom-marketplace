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
 * Laminas HTTP client that OAuth1-signs every outgoing request, replacing the
 * client previously returned by Zend_Oauth_Token_Access::getHttpClient().
 */
class SignedClient extends \Laminas\Http\Client
{
    /**
     * @var string
     */
    private $consumerKey = '';

    /**
     * @var string
     */
    private $consumerSecret = '';

    /**
     * @var string
     */
    private $oauthToken = '';

    /**
     * @var string
     */
    private $oauthTokenSecret = '';

    /**
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $token
     * @param string $tokenSecret
     * @return void
     */
    public function setOauthCredentials($consumerKey, $consumerSecret, $token, $tokenSecret)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->oauthToken = $token;
        $this->oauthTokenSecret = $tokenSecret;
    }

    /**
     * @inheritDoc
     */
    public function send(?\Laminas\Http\Request $request = null)
    {
        $method = $this->getMethod() ?: self::METHOD_GET;
        $requestParams = array_merge($this->getRequest()->getQuery()->toArray(), $this->getRequest()->getPost()->toArray());

        $authorizationHeader = Signature::buildAuthorizationHeader(
            $method,
            (string)$this->getUri(),
            $this->consumerKey,
            $this->consumerSecret,
            $requestParams,
            $this->oauthToken,
            $this->oauthTokenSecret
        );

        $this->setHeaders(['Authorization' => $authorizationHeader]);

        return parent::send($request);
    }
}
