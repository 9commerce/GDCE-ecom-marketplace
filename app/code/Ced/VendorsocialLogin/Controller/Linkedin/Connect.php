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

namespace Ced\VendorsocialLogin\Controller\Linkedin;

/**
 * Class Connect
 * @package Ced\VendorsocialLogin\Controller\Linkedin
 */
class Connect extends \Ced\VendorsocialLogin\Controller\ConnectResponse
{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Linkedin
     */
    protected $_helperLinkedin;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSessionFactory;
    /**
     * @var \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client
     */
    protected $_client;

    /**
     * Connect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Linkedin $helperLinkedin
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Ced\VendorsocialLogin\Model\Linkedin\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Linkedin $helperLinkedin
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_client = $client;
        $this->_helperLinkedin = $helperLinkedin;
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
                __("Some error during Linkedin login.")
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
            return;
        }
        if (!$state || $state != $customerSession->getLinkedinCsrf()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Security check failed. Please try again.')
            );
        }
        $customerSession->setLinkedinCsrf('');
        if ($errorCode) {
            /* Linkedin API read light - abort*/
            if ($errorCode === 'access_denied') {
                $this->messageManager->addNoticeMessage(__('Linkedin Connect process aborted.'));
                return;
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __(sprintf(
                    ("Sorry, %s error occured. Please try again."),
                    $errorCode
                ))
            );
            //return;
        }
        if ($code) {
            $client = $this->_client;
            $token = $code;
            $userInfo = $client->api(
                '/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))'
            );
            $userEmail = $client->api(
                '/emailAddress?q=members&projection=(elements*(handle~))'
            );
            $first_name = !empty($userInfo->firstName->localized->en_US) ? $userInfo->firstName->localized->en_US : '';
            $last_name = !empty($userInfo->lastName->localized->en_US) ? $userInfo->lastName->localized->en_US : '';
            $email = !empty($userEmail->elements[0]->{'handle~'}->emailAddress) ? $userEmail->elements[0]->{'handle~'}->emailAddress : '';

            $oauthUid = !empty($userInfo->id) ? $userInfo->id : '';
            $customersByLinkedinId = $this->_helperLinkedin->getCustomersByLinkedinId($oauthUid);

            if ($customerSession->isLoggedIn()) {
                /* Logged in user*/
                if ($customersByLinkedinId->count()) {
                    /* Linkedin account already connected to other account - deny*/
                    $this->messageManager
                        ->addNoticeMessage(
                            __('Your Linkedin account is already connected to one of our store accounts.')
                        );
                    return;
                }
                /* Connect from account dashboard - attach*/
                $customer = $customerSession->getCustomer();
                $this->_helperLinkedin->connectByLinkedinId(
                    $customer,
                    $oauthUid,
                    $token
                );
                $this->messageManager->addSuccessMessage(
                    __('Your Linkedin account is now connected to your new user accout at our store. You can login next time by the Linkedin SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
                );
                return;
            }

            if ($customersByLinkedinId->count()) {
                /* Existing connected user - login*/
                $customer = $customersByLinkedinId->getFirstItem();
                $this->_helperLinkedin->loginByCustomer($customer);
                $this->messageManager->addSuccessMessage(
                    __('You have successfully logged in using your Linkedin account.')
                );
                return;
            }

            $customersByEmail = $this->_helperLinkedin->getCustomersByEmail($email);
            if ($customersByEmail->count()) {
                /* Email account already exists - attach, login*/
                $customer = $customersByEmail->getFirstItem();
                $this->_helperLinkedin->connectByLinkedinId(
                    $customer->getId(),
                    $oauthUid,
                    $token
                );
                $this->messageManager->addSuccessMessage(
                    __('We find you already have an account at our store. Your Linkedin account is now connected to your store account. Account confirmation mail has been sent to your email.')
                );
                return;
            }

            /* New connection - create, attach, login*/
            if (empty($first_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your Linkedin first name.')
                );
            }

            if (empty($last_name)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, could not retrieve your Linkedin last name.')
                );
            }

            $this->_helperLinkedin->connectByCreatingAccount(
                $email,
                $first_name,
                $last_name,
                $oauthUid,
                $token
            );

            $this->messageManager->addSuccessMessage(
                __('Your Linkedin account is now connected to your new user accout at our store. You can login next time by the Linkedin SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
            );

        }
    }
}
