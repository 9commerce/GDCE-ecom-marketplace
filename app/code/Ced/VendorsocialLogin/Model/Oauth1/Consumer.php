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
 * Minimal OAuth 1.0a consumer (three-legged flow), replacing Zend_Oauth_Consumer.
 *
 * Magento never shipped a Laminas successor for zend-oauth, so this reimplements
 * only what the Twitter "Login with Twitter" flow requires: request token,
 * authorization redirect, and access token exchange.
 */
class Consumer
{
    /**
     * @var string
     */
    private $callbackUrl;

    /**
     * @var string
     */
    private $siteUrl;

    /**
     * @var string
     */
    private $authorizeUrl;

    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * @var Token|null
     */
    private $lastRequestToken;

    /**
     * @param array $options callbackUrl, siteUrl, authorizeUrl, consumerKey, consumerSecret
     */
    public function __construct(array $options)
    {
        $this->callbackUrl = $options['callbackUrl'] ?? '';
        $this->siteUrl = rtrim($options['siteUrl'] ?? '', '/');
        $this->authorizeUrl = $options['authorizeUrl'] ?? '';
        $this->consumerKey = $options['consumerKey'] ?? '';
        $this->consumerSecret = $options['consumerSecret'] ?? '';
    }

    /**
     * Step 1: obtain a request token.
     *
     * @return Token|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequestToken()
    {
        $url = $this->siteUrl . '/request_token';
        $header = Signature::buildAuthorizationHeader(
            'POST',
            $url,
            $this->consumerKey,
            $this->consumerSecret,
            [],
            null,
            null,
            ['oauth_callback' => $this->callbackUrl]
        );

        $response = $this->post($url, $header);
        if ($response->isClientError() || $response->isServerError()) {
            return false;
        }

        $data = [];
        parse_str($response->getBody(), $data);
        if (empty($data['oauth_token']) || empty($data['oauth_token_secret'])) {
            return false;
        }

        return $this->lastRequestToken = new Token($data['oauth_token'], $data['oauth_token_secret'], $data);
    }

    /**
     * Step 2: redirect the browser to Twitter's authorization page.
     *
     * Mirrors Zend_Oauth_Consumer::redirect(), which used the request token obtained
     * by the preceding getRequestToken() call rather than taking one as an argument.
     *
     * @return void
     */
    public function redirect()
    {
        $url = $this->authorizeUrl . '?' . http_build_query(['oauth_token' => $this->lastRequestToken->getToken()]);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Step 3: exchange the verified request token for an access token.
     *
     * @param array $params The callback params (must include oauth_verifier)
     * @param Token|array $requestToken A Token, or the array produced by Token::jsonSerialize()
     *        after it round-tripped through session storage.
     * @return Token|false
     */
    public function getAccessToken(array $params, $requestToken)
    {
        if (empty($params['oauth_verifier'])) {
            return false;
        }

        if (is_array($requestToken)) {
            $requestToken = Token::fromArray($requestToken);
        }

        $url = $this->siteUrl . '/access_token';
        $header = Signature::buildAuthorizationHeader(
            'POST',
            $url,
            $this->consumerKey,
            $this->consumerSecret,
            [],
            $requestToken->getToken(),
            $requestToken->getTokenSecret(),
            ['oauth_verifier' => $params['oauth_verifier']]
        );

        $response = $this->post($url, $header, ['oauth_verifier' => $params['oauth_verifier']]);
        if ($response->isClientError() || $response->isServerError()) {
            return false;
        }

        $data = [];
        parse_str($response->getBody(), $data);
        if (empty($data['oauth_token']) || empty($data['oauth_token_secret'])) {
            return false;
        }

        return new Token($data['oauth_token'], $data['oauth_token_secret'], $data);
    }

    /**
     * @param string $url
     * @param string $authorizationHeader
     * @param array $postParams
     * @return \Laminas\Http\Response
     * @throws \Laminas\Http\Client\Exception\ExceptionInterface
     */
    private function post($url, $authorizationHeader, array $postParams = [])
    {
        $client = new \Laminas\Http\Client($url, ['timeout' => 60]);
        $client->setMethod('POST');
        $client->setHeaders(['Authorization' => $authorizationHeader]);
        if ($postParams) {
            $client->setParameterPost($postParams);
        }

        return $client->send();
    }
}
