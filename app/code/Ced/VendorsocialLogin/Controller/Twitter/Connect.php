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

namespace Ced\VendorsocialLogin\Controller\Twitter;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Connect
 * @package Ced\VendorsocialLogin\Controller\Twitter
 */
class Connect extends \Ced\VendorsocialLogin\Controller\ConnectResponse
{
    /**
     * @var \Ced\VendorsocialLogin\Helper\Twitter
     */
    protected $_helperTwitter;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSessionFactory;
    /**
     * @var \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client
     */
    protected $_client;

    /**
     * @var SerializerInterface
     */
    protected $_serializerInterface;

    /**
     * Connect constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client
     * @param \Ced\VendorsocialLogin\Helper\Twitter $helperTwitter
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client,
        \Ced\VendorsocialLogin\Helper\Twitter $helperTwitter,
        SerializerInterface $serializerInterface
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_client = $client;
        $this->_helperTwitter = $helperTwitter;
        $this->_serializer = $serializerInterface;
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
                __("Some error during Twitter login.")
            );
        }
        return $this->_sendResponse();
    }

    /**
     * connect to twitter account
     */
    protected function _connectCallback()
    {
        $customerSession = $this->_customerSessionFactory->create();
        if (!($params = $this->getRequest()->getParams()) ||
            !($requestToken = $this->_serializer->unserialize($customerSession->getTwitterRequestToken()))
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Twitter Connect process aborted')
            );
           // return;
        }

        $this->referer = $customerSession->getTwitterRedirect();

        if (isset($params['denied'])) {
            $this->messageManager
                ->addNoticeMessage(
                    __('Twitter Connect process aborted.')
                );
            return;
        }

        $client = $this->_client;

        $token = $client->getAccessToken();

        $userInfo = (object)array_merge(
            (array)($userInfo = $client->api('/account/verify_credentials.json', 'GET', ['include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true'])),
            ['email' => sprintf($userInfo->name, strtolower($userInfo->screen_name))]
        );

        $customersByTwitterId = $this->_helperTwitter
            ->getCustomersByTwitterId($userInfo->id);

        if ($customerSession->isLoggedIn()) {
            if ($customersByTwitterId->count()) {
                $this->messageManager
                    ->addNoticeMessage(
                        __('Your Twitter account is already connected to one of our store accounts.')
                    );
                return;
            }

            $customer = $customerSession->getCustomer();
            $this->_helperTwitter->connectByTwitterId(
                $customer,
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccessMessage(
                __('Your Twitter account is now connected to your new user accout at our store. You can login next time by the Twitter SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
            );
            return;
        }

        if ($customersByTwitterId->count()) {
            /* Existing connected user - login*/
            $customer = $customersByTwitterId->getFirstItem();
            $this->_helperTwitter->loginByCustomer($customer);
            $this->messageManager->addSuccessMessage(
                __('You have successfully logged in using your Twitter account.')
            );
            return;
        }

        $customersByEmail = $this->_helperTwitter
            ->getCustomersByEmail($userInfo->email);


        if ($customersByEmail->count()) {
            /* Email account already exists - attach, login*/
            $customer = $customersByEmail->getFirstItem();
            $this->_helperTwitter->connectByTwitterId(
                $customer->getId(),
                $userInfo->id,
                $token
            );

            $this->messageManager->addSuccessMessage(
                __('We find you already have an account at our store. Your Twitter account is now connected to your store account. Account confirmation mail has been sent to your email.')
            );
            return;
        }

        if (empty($userInfo->name)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, could not retrieve your Twitter name. Please try again.')
            );
        }

        $this->_helperTwitter->connectByCreatingAccount(
            $userInfo->email,
            $userInfo->name,
            $userInfo->id,
            $token
        );

        $this->messageManager->addSuccessMessage(
            __('Your Twitter account is now connected to your new user accout at our store. You can login next time by the Twitter SocialLogin button or Store user account. Account confirmation mail has been sent to your email.')
        );

        $this->messageManager->addSuccessMessage(
            sprintf(__('Since Twitter doesn\'t support third-party access to your email address, we were unable to send you your store accout credentials. To be able to login using store account credentials you will need to update your email address and password using our <a href="%s">Edit Account Information</a>.'), $this->urlModel->getUrl(customer / account / edit))
        );
    }
}
