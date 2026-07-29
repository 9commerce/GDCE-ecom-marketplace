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
 * Class Twitter
 * @package Ced\VendorsocialLogin\Helper
 */
class Twitter extends \Magento\Framework\App\Helper\AbstractHelper
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
     * Twitter constructor.
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
    *    connect existing account with Twitter
    *     @param int $customerId
    *    @param string $twitterId
    *    @param string $token
    */
    public function connectByTwitterId(
        $customerId,
        $twitterId,
        $token
    ) {
        $customer = $this->_customerFactory->create();
        $customer->load($customerId);
        $customer->setCedSocialloginGid($twitterId);
        $customer->setCedSocialloginGtoken($token);
        $customer->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }

    /*
    *    connect new account with Twitter
    *    @param string $email
    *    @param string $firstname
    *    @param string $lastname
    *    @param string $twitterId
    *    @param string $token
    */
    public function connectByCreatingAccount(
        $email,
        $name,
        $twitterId,
        $token
    ) {
        $name = explode(' ', $name, 2);

        if (count($name) > 1) {
            $firstName = $name[0];
            $lastName = $name[1];
        } else {
            $firstName = $name[0];
            $lastName = $name[0];
        }

        $customer = $this->_customerFactory->create();
        $customerDetails = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'sendemail' => 0,
            'confirmation' => 0,
            'ced_sociallogin_tid' => $twitterId,
            'ced_sociallogin_ttoken' => $token
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
    *    get customer by twitter id
    *    @param int $twitterId
    *
    *    return \Magento\Customer\Model\Customer $customer
    */
    public function getCustomersByTwitterId($twitterId)
    {
        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('ced_sociallogin_tid', $twitterId)
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
