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


namespace Ced\CsMarketplace\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Vpayments
 * @package Ced\CsMarketplace\Controller
 */
class Vpayments extends Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $csmarketplacesession;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory
     */
    protected $vpaymentCollection;

    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $vpaymentFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Vpayments constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\Session $csmarketplacesession
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory $vpaymentCollection
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\Session $csmarketplacesession,
        \Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory $vpaymentCollection,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
    ) {
        $this->csmarketplacesession = $csmarketplacesession;
        $this->vpaymentCollection = $vpaymentCollection;
        $this->vpaymentFactory = $vpaymentFactory;
        $this->registry = $registry;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Try to load valid order by order_id and register it
     *
     * @param null $paymentId
     * @return bool
     */
    protected function _loadValidPayment($paymentId = null)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        if (null === $paymentId) {
            $paymentId = (int)$this->getRequest()->getParam('payment_id', 0);
        }
        if ($paymentId == 0) {
            $paymentId = (int)$this->getRequest()->getParam('payment_id', 0);
            if (!$paymentId) {
                $this->_forward('noRoute');
                return false;
            }
        }
        if ($paymentId) {
            $vendorId = $this->csmarketplacesession->getVendorId();
            $payment = $this->vpaymentFactory->loadByField(['payment_id'], [$paymentId]);
        }

        if ($this->_canViewPayment($payment)) {
            $this->registry->register('current_vpayment', $payment);
            return true;
        } else {
            $this->_redirect('*/*');
        }
        return false;
    }

    /**
     * Check order view availability
     *
     * @param $payment
     * @return bool
     */
    protected function _canViewPayment($payment)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $vendorId = $this->csmarketplacesession->getVendorId();
        $paymentId = $payment->getId();


        $collection = $this->vpaymentCollection->create();
        $collection->addFieldToFilter('id', $paymentId)
            ->addFieldToFilter('vendor_id', $vendorId);

        if (count($collection) > 0) {
            return true;
        } else {
            return false;
        }
    }
}
