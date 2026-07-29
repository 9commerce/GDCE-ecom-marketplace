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
namespace Ced\VendorsocialLogin\Block\Google;

/**
 * Class Button
 * @package Ced\VendorsocialLogin\Block\Google
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Ced\VendorsocialLogin\Model\google\Oauth2\Client
     */
    protected $_clientGoogle;

    /**
     * @var mixed|null
     */
    protected $userInfo = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Button constructor.
     * @param \Ced\VendorsocialLogin\Model\google\Oauth2\Client $clientGoogle
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\VendorsocialLogin\Model\google\Oauth2\Client $clientGoogle,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_clientGoogle = $clientGoogle;
        $this->_registry = $registry;
        $this->_customerSession = $customerSession;
        $this->userInfo = $this->_registry->registry('ced_sociallogin_google_userdetails');
        parent::__construct($context, $data);
    }

    /**
     * Button construct
     */
    protected function _construct()
    {
        parent::_construct();
        // CSRF protection
        $this->_customerSession->setGoogleCsrf($csrf = hash('sha256', uniqid(rand(), true)));
        $this->_clientGoogle->setState($csrf);
    }

    /**
     * @return string
     */
    public function getButtonUrl()
    {
        if (empty($this->userInfo)) {
            return $this->_clientGoogle->createAuthUrl();
        } else {
            return $this->getUrl('cedsociallogin/google/disconnect');
        }
    }

    /**
     * @return mixed
     */
    public function getButtonText()
    {
        if (empty($this->userInfo)) {
            if (!($text = $this->_registry->registry('ced_sociallogin_button_text'))) {
                $text = $this->__('Connect');
            }
        } else {
            $text = $this->__('Disconnect');
        }
        return $text;
    }

    /**
     * @return string
     */
    public function getButtonClass()
    {
        if (empty($this->userInfo)) {
            $text = "ced_google_connect";
        } else {
            $text = "ced_google_disconnect";
        }
        return $text;
    }
}
