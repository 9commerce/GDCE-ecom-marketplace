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
namespace Ced\VendorsocialLogin\Block;

/**
 * Class Container
 * @package Ced\VendorsocialLogin\Block
 */
abstract class Container extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client
     */
    protected $_clientFacebook;

    /**
     * @var \Ced\VendorsocialLogin\Model\Google\Oauth2\Client
     */
    protected $_clientGoogle;

    /**
     * @var \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client
     */
    protected $_clientTwitter;

    /**
     * @var \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client
     */
    protected $_clientLinkedin;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var int
     */
    protected $numEnabled = 0;

    /**
     * @var int
     */
    protected $numDescShown = 0;

    /**
     * @var int
     */
    protected $numButtShown = 0;

    /**
     * Container constructor.
     * @param \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $clientFacebook
     * @param \Ced\VendorsocialLogin\Model\Google\Oauth2\Client $clientGoogle
     * @param \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $clientTwitter
     * @param \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $clientLinkedin
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $clientFacebook,
        \Ced\VendorsocialLogin\Model\Google\Oauth2\Client $clientGoogle,
        \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $clientTwitter,
        \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $clientLinkedin,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->_clientFacebook = $clientFacebook;
        $this->_clientGoogle = $clientGoogle;
        $this->_clientTwitter = $clientTwitter;
        $this->_clientLinkedin = $clientLinkedin;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        $this->customerSession->setRefererUrl($this->_request->getPathInfo());
        if (!$this->googleEnabled() &&
            !$this->facebookEnabled() &&
            !$this->twitterEnabled() &&
            !$this->linkedinEnabled()) {
            return parent::__construct($context);
        }
        if ($this->googleEnabled()) {
            $this->numEnabled++;
        }
        if ($this->facebookEnabled()) {
            $this->numEnabled++;
        }
        if ($this->twitterEnabled()) {
            $this->numEnabled++;
        }
        if ($this->linkedinEnabled()) {
            $this->numEnabled++;
        }
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function _getColSet()
    {
        return 'col' . $this->numEnabled . '-set';
    }

    /**
     * @return string
     */
    public function _getDescCol()
    {
        return 'col-' . ++$this->numDescShown;
    }

    /**
     * @return string
     */
    public function _getButtCol()
    {
        return 'col-' . ++$this->numButtShown;
    }

    /**
     * @return bool
     */
    public function facebookEnabled()
    {
        return $this->_clientFacebook->isEnabled();
    }

    /**
     * @return bool
     */
    public function googleEnabled()
    {
        return $this->_clientGoogle->isEnabled();
    }

    /**
     * @return bool
     */
    public function twitterEnabled()
    {
        return $this->_clientTwitter->isEnabled();
    }

    /**
     * @return bool
     */
    public function linkedinEnabled()
    {
        return $this->_clientLinkedin->isEnabled();
    }
}
