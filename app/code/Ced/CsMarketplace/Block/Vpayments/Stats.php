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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vpayments;

use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class Stats
 * @package Ced\CsMarketplace\Block\Vpayments
 */
class Stats extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Stats constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Payment $paymentHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Payment $paymentHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->paymentHelper = $paymentHelper;
        $this->_priceCurrency = $priceCurrency;

        $this->setPendingAmount(0.00);
        $this->setPendingTransfers(0);
        $this->setPaidAmount(0.00);
        $this->setCanceledAmount(0.00);
        $this->setRefundableAmount(0.00);
        $this->setRefundedAmount(0.00);
        $this->setEarningAmount(0.00);

        if ($this->getVendor() && $this->getVendor()->getId()) {
            $collection = $this->paymentHelper->_getTransactionsStats($this->getVendor());

            if (count($collection) > 0) {
                foreach ($collection as $stats) {

                    switch ($stats->getPaymentState()) {
                        case \Ced\CsMarketplace\Model\Vorders::STATE_OPEN :
                            $this->setPendingAmount($stats->getNetAmount());
                            $this->setPendingTransfers($stats->getCount() ? $stats->getCount() : 0);
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_PAID :
                            $this->setPaidAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_CANCELED :
                            $this->setCanceledAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_REFUND :
                            $this->setRefundableAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_REFUNDED :
                            $this->setRefundedAmount($stats->getNetAmount());
                            break;
                    }
                }
            }
            $earnedAmountCollection = $this->getVendor()->getAssociatedPayments();
            $earnedAmount = 0;
            $netEarned = 0;
            if($earnedAmountCollection->count()>0){
                foreach($earnedAmountCollection as $paymentData){
                    $earnedAmount+=$paymentData->getBalance();
                    $netEarned += $paymentData->getBaseNetAmount();
                }
            }

            $this->setEarningAmount($netEarned);
        }
    }

    /**
     * @param $price
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param $currency
     * @return float
     */
    public function formatCurrency(
        $price,
        $includeContainer = false,
        $precision = 2,
        $scope = null,
        $currency = ''
    )
    {
        return $this->_priceCurrency->format(
            $price,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }

    /**
     * @return int
     */
    public function getPendingAmount()
    {
        $collection = $this->paymentHelper->_getVendorTransactionsStats($this->getVendor(),
            \Ced\CsMarketplace\Model\Vorders::STATE_OPEN);
        $pending = 0;
        if($collection)
            $pending = $collection->getFirstItem()->getPendingAmount();
        return $pending;
    }
}
