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

namespace Ced\CsMarketplace\Controller\Vpayments;

use Ced\CsMarketplace\Helper\Payment;
use Ced\CsMarketplace\Model\Vpayment;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Ced\CsMarketplace\Model\Session as MarketplaceSession;

/**
 * Class View
 * @package Ced\CsMarketplace\Controller\Vpayments
 */
class View extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public $_vpayment;
    /**
     * @var Payment
     */
    public $_payment;
    /**
     * @var Registry|null
     */
    public $_coreRegistry = null;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MarketplaceSession
     */
    protected $mktSession;

    /**
     * View constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Vpayment $vpayment
     * @param Payment $payment
     * @param Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        Vpayment $vpayment,
        Payment $payment,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        MarketplaceSession $mktSession
    )
    {
        $this->_vpayment = $vpayment;
        $this->_coreRegistry = $registry;
        $this->_payment = $payment;
        $this->mktSession = $mktSession;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );

    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        if (!$this->_loadValidPayment()) {
            return false;
        }

        $resultPage = $this->resultPageFactory->create();


        $resultPage->getConfig()->getTitle()->set(__('Transaction Details'));
        return $resultPage;
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
            $paymentId = (int)$this->getRequest()->getParam('payment_id');
        }
        if (!$paymentId) {
            $this->_forward('noRoute');
            return false;
        }
        $payment = $this->_vpayment->load($paymentId);
        $paymentModel = $this->_canViewPayment($payment);

        if ($paymentModel) {
            $this->_coreRegistry->register('current_vpayment', $payment);
            return true;
        }
        $this->_redirect('csmarketplace/vpayments/');
        return false;
    }

    /**
     * Check order view availability
     * @param $payment
     * @return bool
     */
    protected function _canViewPayment($payment)
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $vendorId = $this->mktSession->getVendorId();
        $paymentId = $payment->getId();


        $collection = $this->_vpayment->getCollection();
        $collection->addFieldToFilter('entity_id', $paymentId)
            ->addFieldToFilter('vendor_id', $vendorId);

        if (count($collection) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Export Payment Action
     */
    public function exportCsvAction()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $filename = 'vendor_transactions.csv';
        $content = $this->_payment->getVendorCommision();
        $this->_prepareDownloadResponse($filename, $content);

    }
}
