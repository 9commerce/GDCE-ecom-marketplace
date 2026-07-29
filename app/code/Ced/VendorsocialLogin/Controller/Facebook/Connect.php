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

namespace Ced\VendorsocialLogin\Controller\Facebook;

/**
 * Class Connect
 * @package Ced\VendorsocialLogin\Controller\Facebook
 */
class Connect extends \Ced\VendorsocialLogin\Controller\ConnectResponse
{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Facebook
     */
    protected $_helperFacebook;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * @var \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client
     */
    protected $_client;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Connect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Facebook $helperFacebook
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Facebook $helperFacebook,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_client = $client;
        $this->_helperFacebook = $helperFacebook;
        $this->redirect = $redirect;
        parent::__construct($context, $customerSessionFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Exception
     * @throws \Magento\Framework\Exception
     */
    public function execute()
    {
        try {
            $this->_connectCallback();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __("Some error during Facebook login.")
            );
        }
        return $this->_sendResponse();
    }

    /**
     * @throws \Ced\VendorsocialLogin\Model\Facebook\Oauth2\Exception
     * @throws \Magento\Framework\Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function _connectCallback()
    {
        $customerSession = $this->_customerSessionFactory->create();
        $errorCode = $this->getRequest()->getParam('error');
        $code = $this->getRequest()->getParam('code');
        $state = $this->getRequest()->getParam('state');
        if (!($errorCode || $code) && !$state) {
            /* Direct route access - deny*/
            return;
        }
        if (!$state || $state != $customerSession->getFacebookCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Security check failed. Please try again.')
            );

        }
        $customerSession->setFacebookCsrf('');
        if ($errorCode) {
            /* Facebook API read light - abort*/
            if ($errorCode === 'access_denied') {
                $this->messageManager
                    ->addNoticeMessage(
                        __('Facebook Connect process aborted.')
                    );
                return;
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                sprintf(
                    __('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );
           // return;
        }

        if ($code) {
            $client = $this->_client;
            $userInfo = $client->api('/me?fields=id,name,first_name,last_name,email');
            $token = $client->getAccessToken();
            $customersByFacebookId = $this->_helperFacebook
                ->getCustomersByFacebookId($userInfo->id);

            if ($customerSession->isLoggedIn()) {
                /* Logged in user*/
                if ($customersByFacebookId->count()) {
                    /* Facebook account already connected to other account - deny*/
                    $this->messageManager
                        ->addNoticeMessage(
                            __('Your Facebook account is already connected to one of our store accounts.')
                        );
                    return;
                }

                /* Connect from account dashboard - attach*/
                $customer = $customerSession->getCustomer();
                $this->_helperFacebook->connectByFacebookId(
                    $customer,
                    $userInfo->id,
                    $token
                );
                $this->messageManager->addSuccessMessage(
                    __('Your Facebook account is now connected to your new user accout at our store.
                    You can login next time by the Facebook SocialLogin button or Store user account.
                    Account confirmation mail has been sent to your email.')
                );
                return;
            }

            if ($customersByFacebookId->count()) {
                /* Existing connected user - login*/
                $customer = $customersByFacebookId->getFirstItem();
                $this->_helperFacebook->loginByCustomer($customer);
                $this->messageManager->addSuccessMessage(
                    __('You have successfully logged in using your Facebook account.')
                );
                return;
            }

            $customersByEmail = $this->_helperFacebook
                ->getCustomersByEmail($userInfo->email);

            if ($customersByEmail->count()) {
                /* Email account already exists - attach, login*/
                $customer = $customersByEmail->getFirstItem();
                $this->_helperFacebook->connectByFacebookId(
                    $customer->getId(),
                    $userInfo->id,
                    $token
                );

                $this->messageManager->addSuccessMessage(
                    __('We find you already have an account at our store.
                    Your Facebook account is now connected to your store account.
                    Account confirmation mail has been sent to your email.')
                );

                return;
            }

            /* New connection - create, attach, login*/
            if (empty($userInfo->first_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your Facebook first name. Please try again.')
                );
            }

            if (empty($userInfo->last_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your Facebook last name. Please try again.')
                );
            }

            $this->_helperFacebook->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->first_name,
                $userInfo->last_name,
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccessMessage(
                __('Your Facebook account is now connected to your new user accout at our store.
                You can login next time by the Facebook SocialLogin button or Store user account.
                Account confirmation mail has been sent to your email.')
            );
        }
    }
}
