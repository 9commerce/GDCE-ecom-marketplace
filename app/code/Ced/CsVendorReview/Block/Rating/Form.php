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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Block\Rating;

class Form extends \Magento\Framework\View\Element\Template
{

    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * Form constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
        \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->ratingCollection = $ratingCollection;
        $vendor_id = $this->getVendor()->getCustomerId();
        $customer = $session->getCustomer();
        if ($customer && $customer->getId()) {
            $this->setCustomername($customer->getFirstname());
            $this->setCustomerid($customer->getId());
        }

        $this->setAllowWriteReviewFlag($session->isLoggedIn());
        $this->setCustomerIsVendor($session->isLoggedIn() && $customer->getId() == $vendor_id);
        $this->setLoginLink($this->getUrl('customer/account/login/'));
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor) {
            $this->_vendor = $this->registry->registry('current_vendor');
        }
        return $this->_vendor;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->getVendor()->getId();
    }

    /**
     * @return array
     */
    public function getRatingOption()
    {
        return [
            '0' => __('Please Select Option'),
            '20' => __('1 OUT OF 5'),
            '40' => __('2 OUT OF 5'),
            '60' => __('3 OUT OF 5'),
            '80' => __('4 OUT OF 5'),
            '100' => __('5 OUT OF 5')
        ];
    }

    /**
     * @return mixed
     */
    public function getRatings()
    {
        return $this->ratingCollection->create()->setOrder('sort_order', 'ASC');
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('csvendorreview/rating/post');
    }
}
