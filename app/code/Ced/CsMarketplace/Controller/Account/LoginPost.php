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
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Account;

use Ced\CsMarketplace\Model\Account\Redirect as AccountRedirect;
use Ced\CsMarketplace\Model\Url as CustomerUrl;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Class LoginPost
 * @package Ced\CsMarketplace\Controller\Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var AccountManagementInterface
     */
    protected $vendorAccountManagement;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var VendorAcRedirect
     */
    protected $vendorAcRedirect;

    /**
     * @var VendorUrl
     */
    protected $vendorUrl;

    /**
     * @var VendorSession
     */
    protected $vendorSession;
    /**
     * @var \Ced\CsMarketplace\Helper\Cookie
     */
    protected $_cookieData;

    /**
     * LoginPost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $vendorAccountManagement
     * @param CustomerUrl $vendorHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $vendorAcRedirect
     * @param \Ced\CsMarketplace\Helper\Cookie $cookieData
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $vendorAccountManagement,
        CustomerUrl $vendorHelperData,
        Validator $formKeyValidator,
        AccountRedirect $vendorAcRedirect,
        \Ced\CsMarketplace\Helper\Cookie $cookieData
    ) {
        $this->vendorSession = $customerSession;
        $this->vendorAccountManagement = $vendorAccountManagement;
        $this->vendorUrl = $vendorHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->vendorAcRedirect = $vendorAcRedirect;
        $this->_cookieData = $cookieData;
        parent::__construct($context);
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->vendorSession->isLoggedIn()) {
            /**
             * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
             */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('csmarketplace/vendor/index');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {

            $login = $this->getRequest()->getPost('login');

            if (!empty($login['username']) && !empty($login['password'])) {
                try {

                    $customer = $this->vendorAccountManagement->authenticate($login['username'], $login['password']);
                    $this->vendorSession->setCustomerDataAsLoggedIn($customer);
                    $this->vendorSession->regenerateId();

                    if ($this->getRequest()->getPost('remember')) {

                        $cookieInformation = ['userId' => $customer->getId(), 'userEmail' => trim($login['username']),
                            'userPass' => trim(base64_encode($login['password'])), 'rememberMeCheckbox' => 1];
                        $cookieInfoData = json_encode($cookieInformation);

                        $this->_cookieData->set(\Ced\CsMarketplace\Helper\Cookie::VENDOR_COOKIENAME, $cookieInfoData);
                    } else {
                        $this->_cookieData->delete(\Ced\CsMarketplace\Helper\Cookie::VENDOR_COOKIENAME);
                    }

                    /**@note: code for remember me */

                } catch (\Magento\Framework\Exception\EmailNotConfirmedException $e) {
                    $value = $this->vendorUrl->getEmailConfirmationUrl($login['username']);
                    $text = 'This account is not confirmed. <a href="'.$value.'">Click here</a> to resend confirmation email.';
                    $this->messageManager->addComplexErrorMessage('addCustomSuccessMessage', [
                            'html' => $text,
                            'params' => $value
                        ]
                    );
                    $this->vendorSession->setUsername($login['username']);
                } catch (\Magento\Framework\Exception\InvalidEmailOrPasswordException $e) {
                    $message = __('Invalid email or password.');
                    $this->messageManager->addErrorMessage($message);
                    $this->vendorSession->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid email or password.');
                    $this->messageManager->addErrorMessage($message);
                    $this->vendorSession->setUsername($login['username']);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }
        return $this->vendorAcRedirect->getRedirect();
    }
}
