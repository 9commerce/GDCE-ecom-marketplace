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

use Ced\CsMarketplace\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class CreatePassword
 * @package Ced\CsMarketplace\Controller\Account
 */
class CreatePassword extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * CreatePassword constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param AccountManagement $accountManagement
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        AccountManagement $accountManagement,
        \Magento\Framework\Registry $registry
    ) {
        $this->_registry = $registry;
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        if (!$this->_registry->registry('vendorPanel'))
            $this->_registry->register('vendorPanel', 1);
        parent::__construct($context);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $customerId = (int)$this->getRequest()->getParam('id');
        $isDirectLink = $resetPasswordToken != '' && $customerId != 0;
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
            $customerId = (int)$this->session->getRpCustomerId();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken($customerId, $resetPasswordToken);
            if ($isDirectLink) {

                $this->session->setRpToken($resetPasswordToken);
                $this->session->setRpCustomerId($customerId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');
                $this->_eventManager->dispatch(
                    'ced_csmarketplace_predispatch_action', [
                        'session' => $this->session,
                    ]
                );
                return $resultRedirect;
            } else {

                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()->getBlock('vendor.resetPassword')->setCustomerId($customerId)
                    ->setResetPasswordLinkToken($resetPasswordToken);
                $this->_eventManager->dispatch(
                    'ced_csmarketplace_predispatch_action', [
                        'session' => $this->session,
                    ]
                );
                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');
            return $resultRedirect;
        }
    }
}
