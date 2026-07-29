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
 * Class Google
 * @package Ced\VendorsocialLogin\Helper
 */
class Google extends \Magento\Framework\App\Helper\AbstractHelper
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
     * Google constructor.
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

    /*
    *    connect existing account with Google
    *     @param int $customerId
    *    @param string $googleId
    *    @param string $token
    */
    public function connectByGoogleId(
        $customerId,
        $googleId,
        $token
    ) {
        $customer = $this->_customerFactory->create();
        $customer->load($customerId);
        $customer->setCedSocialloginGid($googleId);
        $customer->setCedSocialloginGtoken($token);
        $customer->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /**
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $googleId
     * @param $token
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function connectByCreatingAccount(
        $email,
        $firstName,
        $lastName,
        $googleId,
        $token
    ) {
        $customer = $this->_customerFactory->create();
        $customerDetails = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'sendemail' => 0,
            'confirmation' => 0,
            'ced_sociallogin_gid' => $googleId,
            'ced_sociallogin_gtoken' => $token
        ];
        $customer->setData($customerDetails);
        $customer->save();
        $customer->sendNewAccountEmail('confirmed', '');
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /*
    *    login by customer
    *    @param \Magento\Customer\Model\Customer $customer
    */
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)
    {
        if ($customer->getConfirmation()) {
            $customer->setConfirmation(null);
            $customer->save();
        }
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /*
    *    get customer by google id
    *    @param int $googleId
    *
    *    return \Magento\Customer\Model\Customer $customer
    */
    public function getCustomersByGoogleId($googleId)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('ced_sociallogin_gid', $googleId)
            ->setPage(1, 1);

        return $collection;
    }

    /*
    *    get customer by email id
    *    @param string $email
    *
    *    return \Magento\Customer\Model\Customer $customer
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
