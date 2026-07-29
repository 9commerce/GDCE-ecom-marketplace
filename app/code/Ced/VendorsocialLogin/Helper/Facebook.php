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

namespace Ced\VendorsocialLogin\Helper;

/**
 * Class Facebook
 * @package Ced\VendorsocialLogin\Helper
 */
class Facebook extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Facebook constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * @param $customerId
     * @param $facebookId
     * @param $token
     * @throws \Exception
     */
    public function connectByFacebookId(
        $customerId,
        $facebookId,
        $token
    ) {
        $customer = $this->_customerFactory->create();
        $customer->load($customerId);
        $customer->setCedSocialloginFid($facebookId);
        $customer->setCedSocialloginFtoken($token);
        $customer->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $facebookId
     * @param $token
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function connectByCreatingAccount(
        $email,
        $firstName,
        $lastName,
        $facebookId,
        $token
    ) {
        $customer = $this->_customerFactory->create();
        $customerDetails = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'sendemail' => 0,
            'confirmation' => 0,
            'ced_sociallogin_fid' => $facebookId,
            'ced_sociallogin_ftoken' => $token
        ];
        $customer->setData($customerDetails);
        $customer->save();
        $customer->sendNewAccountEmail('confirmed', '');
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @throws \Exception
     */
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)
    {
        if ($customer->getConfirmation()) {

            $customer->setConfirmation(null);

            $customer->save();

        }

        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param $facebookId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomersByFacebookId($facebookId)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('ced_sociallogin_fid', $facebookId)
            ->setPage(1, 1);

        return $collection;
    }

    /**
     * @param $email
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomersByEmail($email)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('email', $email)
            ->setPage(1, 1);

        return $collection;
    }
}
