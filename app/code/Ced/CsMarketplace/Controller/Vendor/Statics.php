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

namespace Ced\CsMarketplace\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Statics
 * @package Ced\CsMarketplace\Controller\Vendor
 */
class Statics extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrencyInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_localeCurrency;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendor;

    /**
     * @var \Ced\CsMarketplace\Helper\Report
     */
    protected $report;

    /**
     * @var \Ced\CsMarketplace\Helper\Payment
     */
    protected $payment;

    /**
     * Statics constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param PriceCurrencyInterface $priceCurrencyInterface
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param StoreManagerInterface $storeManager
     * @param \Ced\CsMarketplace\Helper\Report $report
     * @param \Ced\CsMarketplace\Helper\Payment $payment
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
        PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Framework\Locale\Currency $localeCurrency,
        StoreManagerInterface $storeManager,
        \Ced\CsMarketplace\Helper\Report $report,
        \Ced\CsMarketplace\Helper\Payment $payment
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);
        $this->_priceCurrencyInterface = $priceCurrencyInterface;
        $this->storeManager = $storeManager;
        $this->_localeCurrency = $localeCurrency;
        $this->resultJsonFactory = $jsonFactory;
        $this->vendor = $vendor;
        $this->report = $report;
        $this->payment = $payment;
    }


    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        $resultJson = $this->resultJsonFactory->create();
        if ($vendorId = $this->_getSession()->getVendorId()) {
            $vendor = $this->vendor->create()->load($vendorId);
            $data = $this->getPendingAmount($vendor);
            $result['pendingAmount_total'] = $data['total'];
            $result['pendingAmount_action'] = $data['action'];
            $data = $this->getEarnedAmount($vendor);
            $result['earnedAmount_total'] = $data['total'];
            $result['earnedAmount_action'] = $data['action'];
            $data = $this->getOrdersPlaced($vendor);
            $result['ordersPlaced_total'] = $data['total'];
            $result['ordersPlaced_action'] = $data['action'];
            $data = $this->getProductsSold($vendor);
            $result['productsSold_total'] = $data['total'];
            $result['productsSold_action'] = $data['action'];
        }
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * @param $vendor
     * @return array
     */
    public function getPendingAmount($vendor)
    {
        $data = ['total' => 0, 'action' => ''];
        if ($vendorId = $vendor->getId()) {
            $pendingAmount = 0;

            $ordersCollection = $this->payment->_getVendorTransactionsStats($vendor,
                \Ced\CsMarketplace\Model\Vorders::STATE_OPEN);
            if(is_object($ordersCollection)){
                if(count($ordersCollection->getData()))
                    $pendingAmount = $ordersCollection->getFirstItem()->getPendingAmount();
            }

            $data['total'] = $this->_priceCurrencyInterface->format($pendingAmount, false, 2, null, $this->storeManager->getStore()->getBaseCurrency()->getCode());
            $data['action'] = $this->_url->getUrl('*/vpayments/',
                ['_secure' => true, 'order_payment_state' => 2, 'payment_state' => 1]
            );
        }
        return $data;
    }

    /**
     * Get vendor's Earned Amount data
     * @param $vendor
     * @return array
     */
    public function getEarnedAmount($vendor)
    {
        $data = ['total' => 0, 'action' => ''];
        if ($vendor && $vendor->getId()) {
            $earnedAmountCollection = $vendor->getAssociatedPayments();
            $netAmount = 0;
            $netEarned = 0;
            if ($earnedAmountCollection->count() > 0) {
                foreach ($earnedAmountCollection as $paymentData) {
                    $netAmount += $paymentData->getBalance();
                    $netEarned += $paymentData->getBaseNetAmount();
                }
            }

            $data['total'] = $this->_priceCurrencyInterface->format($netEarned, false, 2, null, $this->storeManager->getStore()->getBaseCurrency()->getCode());
            $data['action'] = $this->_url->getUrl('*/vpayments/', ['_secure' => true]);
        }
        return $data;
    }


    /**
     * @param $vendor
     * @return array
     */
    public function getOrdersPlaced($vendor)
    {
        // Total Orders Placed
        $data = ['total' => 0, 'action' => ''];
        if ($vendor && $vendor->getId()) {
            $ordersCollection = $vendor->getAssociatedOrders();
            $order_total = count($ordersCollection);

            $data['total'] = $order_total;
            $data['action'] = $this->_url->getUrl('*/vorders/', ['_secure' => true]);
        }
        return $data;
    }


    /**
     * Get vendor's Products Sold data
     * @param $vendor
     * @return array
     */
    public function getProductsSold($vendor)
    {
        // Total Products Sold
        $data = ['total' => 0, 'action' => ''];
        if ($vendorId = $vendor->getId()) {
            $productsSold = $this->report
                ->getVproductsReportModel($vendorId, '', '', false)->getFirstItem()->getData('ordered_qty');

            $data['total'] = round($productsSold ?? 0);
            $data['action'] = $this->_url->getUrl('*/vreports/vproducts', ['_secure' => true]);
        }
        return $data;
    }

}
