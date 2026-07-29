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

namespace Ced\VendorsocialLogin\Controller\Google;

/**
 * Class Connect
 * @package Ced\VendorsocialLogin\Controller\Google
 */
class Connect extends \Ced\VendorsocialLogin\Controller\ConnectResponse
{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Google
     */
    protected $_helperGoogle;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSessionFactory;
    /**
     * @var \Ced\VendorsocialLogin\Model\Google\Oauth2\Client
     */
    protected $_client;

    /**
     * Connect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Ced\VendorsocialLogin\Model\Google\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Google $helperGoogle
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Ced\VendorsocialLogin\Model\Google\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Google $helperGoogle
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_client = $client;
        $this->_helperGoogle = $helperGoogle;
        parent::__construct($context, $customerSessionFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
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
                __("Some error during Google login.")
            );
        }
        return $this->_sendResponse();
    }

    /**
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
            // Direct route access - deny
            return;
        }

        if (!$state || $state != $customerSession->getGoogleCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Security check failed. Please try again.')
            );
        }

        $customerSession->setGoogleCsrf('');
        if ($errorCode) {
            // Google API read light - abort
            if ($errorCode === 'access_denied') {
                $this->messageManager
                    ->addNoticeMessage(
                        __('Google Connect process aborted.')
                    );
                return;
            }
            throw new \Magento\Framework\Exception(
                sprintf(
                    __('Sorry, "%s" error occured. Please try again.'),
                    $errorCode
                )
            );
            //return;
        }
        if ($code) {
            $client = $this->_client;
            $userInfo = $client->api('/userinfo');
            $token = $client->getAccessToken();
            $customersByGoogleId = $this->_helperGoogle
                ->getCustomersByGoogleId($userInfo->id);

            if ($customerSession->isLoggedIn()) {
                // Logged in user
                if ($customersByGoogleId->count()) {
                    // Google account already connected to other account - deny
                    $this->messageManager
                        ->addNoticeMessage(
                            __('Your Google account is already connected to one of our store accounts.')
                        );
                    return;
                }
                // Connect from account dashboard - attach
                $customer = $customerSession->getCustomer();
                $this->_helperGoogle->connectByGoogleId(
                    $customer,
                    $userInfo->id,
                    $token
                );
                $this->messageManager->addSuccessMessage(
                    __('Your Google account is now connected to your new user accout at our store. You can login next time by the Google SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
                );
                return;
            }
            if ($customersByGoogleId->count()) {
                // Existing connected user - login
                $customer = $customersByGoogleId->getFirstItem();
                $this->_helperGoogle->loginByCustomer($customer);
                $this->messageManager->addSuccessMessage(
                    __('You have successfully logged in using your Google account.')
                );
                return;
            }
            $customersByEmail = $this->_helperGoogle
                ->getCustomersByEmail($userInfo->email);
            if ($customersByEmail->count()) {
                // Email account already exists - attach, login
                $customer = $customersByEmail->getFirstItem();
                $this->_helperGoogle->connectByGoogleId(
                    $customer->getId(),
                    $userInfo->id,
                    $token
                );
                $this->messageManager->addSuccessMessage(
                    __('We find you already have an account at our store. Your Google account is now connected to your store account. Account confirmation mail has been sent to your email.')
                );
                return;
            }
            // New connection - create, attach, login
            if (empty($userInfo->given_name)) {
                throw new \Magento\Framework\Exception(
                    __('Sorry, could not retrieve your Google first name. Please try again.')
                );
            }
            if (empty($userInfo->family_name)) {
                throw new \Magento\Framework\Exception(
                    __('Sorry, could not retrieve your Google last name. Please try again.')
                );
            }
            $this->_helperGoogle->connectByCreatingAccount(
                $userInfo->email,
                $userInfo->given_name,
                $userInfo->family_name,
                $userInfo->id,
                $token
            );
            $this->messageManager->addSuccessMessage(
                __('Your Google account is now connected to your new user accout at our store. You can login next time by the Google SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
            );
        }
    }
}
