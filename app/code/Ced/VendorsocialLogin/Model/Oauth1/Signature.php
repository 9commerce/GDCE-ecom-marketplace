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
 * Minimal OAuth 1.0a (RFC 5849) HMAC-SHA1 request signing helper.
 *
 * Replaces the request-signing responsibilities previously provided by
 * Zend_Oauth_Consumer / Zend_Oauth_Client, which no longer ship with
 * Magento (no laminas-oauth successor package exists).
 */
class Signature
{
    /**
     * Build a complete "Authorization: OAuth ..." header value.
     *
     * @param string $method HTTP method (GET/POST/DELETE)
     * @param string $url Base URL (no query string)
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param array $requestParams All params sent with the request (query + body), used in the signature only
     * @param string|null $token
     * @param string|null $tokenSecret
     * @param array $extraOauthParams Additional oauth_* params (e.g. oauth_verifier, oauth_callback)
     * @return string
     */
    public static function buildAuthorizationHeader(
        $method,
        $url,
        $consumerKey,
        $consumerSecret,
        array $requestParams = [],
        $token = null,
        $tokenSecret = null,
        array $extraOauthParams = []
    ) {
        $oauthParams = array_merge(
            [
                'oauth_consumer_key' => $consumerKey,
                'oauth_nonce' => bin2hex(random_bytes(16)),
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp' => (string)time(),
                'oauth_version' => '1.0',
            ],
            $extraOauthParams
        );

        if ($token !== null && $token !== '') {
            $oauthParams['oauth_token'] = $token;
        }

        $oauthParams['oauth_signature'] = self::sign(
            $method,
            $url,
            array_merge($requestParams, $oauthParams),
            $consumerSecret,
            $tokenSecret
        );

        $pieces = [];
        foreach ($oauthParams as $key => $value) {
            $pieces[] = self::encode($key) . '="' . self::encode($value) . '"';
        }

        return 'OAuth ' . implode(', ', $pieces);
    }

    /**
     * Compute the OAuth 1.0a HMAC-SHA1 signature for a request.
     *
     * @param string $method
     * @param string $url
     * @param array $allParams Every protocol + request parameter to be signed
     * @param string $consumerSecret
     * @param string|null $tokenSecret
     * @return string Base64-encoded signature
     */
    public static function sign($method, $url, array $allParams, $consumerSecret, $tokenSecret = null)
    {
        $baseString = self::buildBaseString($method, $url, $allParams);
        $key = self::encode($consumerSecret) . '&' . self::encode((string)$tokenSecret);

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }

    /**
     * Build the OAuth 1.0a signature base string.
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @return string
     */
    private static function buildBaseString($method, $url, array $params)
    {
        $normalizedParams = [];
        foreach ($params as $key => $value) {
            $normalizedParams[] = self::encode($key) . '=' . self::encode($value);
        }
        sort($normalizedParams);

        return strtoupper($method) . '&' . self::encode($url) . '&' . self::encode(implode('&', $normalizedParams));
    }

    /**
     * RFC 3986 percent-encoding, as mandated by RFC 5849 (unlike PHP's urlencode()).
     *
     * @param string $value
     * @return string
     */
    public static function encode($value)
    {
        return str_replace('%7E', '~', rawurlencode((string)$value));
    }
}
