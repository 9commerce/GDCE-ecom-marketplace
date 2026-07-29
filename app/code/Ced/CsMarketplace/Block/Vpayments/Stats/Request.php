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


namespace Ced\CsMarketplace\Block\Vpayments\Stats;

use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\Vorders;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class Request
 * @package Ced\CsMarketplace\Block\Vpayments\Stats
 */
class Request extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $requested;

    /**
     * Request constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Payment $paymentHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $requested
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Payment $paymentHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\Vpayment\Requested $requested
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->requested = $requested;
        $this->paymentHelper = $paymentHelper;
        $this->csmarketplaceHelper = $csmarketplaceHelper;

        $this->setPendingAmount(0.00);
        $this->setPendingTransfers(0);
        $this->setPaidAmount(0.00);
        $this->setCanceledAmount(0.00);
        $this->setRefundableAmount(0.00);
        $this->setRefundedAmount(0.00);
        $this->setEarningAmount(0.00);

        if ($this->getVendor() && $this->getVendor()->getId()) {
            $collectionTransaction = $paymentHelper->_getTransactionsStats($this->getVendor());

            if (!empty($collectionTransaction)) {
                foreach ($collectionTransaction as $statsRecord) {
                    switch ($statsRecord->getPaymentState()) {
                        case Vorders::STATE_REFUND :
                            $this->setRefundableAmount($statsRecord->getNetAmount());
                            break;

                        case Vorders::STATE_REFUNDED :
                            $this->setRefundedAmount($statsRecord->getNetAmount());
                            break;

                        case Vorders::STATE_OPEN :
                            $this->setPendingTransfers($statsRecord->getCount() ? $statsRecord->getCount() : 0);
                            $this->setPendingAmount($statsRecord->getNetAmount());
                            break;

                        case Vorders::STATE_CANCELED :
                            $this->setCanceledAmount($statsRecord->getNetAmount());
                            break;

                        case Vorders::STATE_PAID :
                            $this->setPaidAmount($statsRecord->getNetAmount());
                            break;
                    }
                }
            }

            $marketplaceHelper = $this->csmarketplaceHelper;
            $main_table = $marketplaceHelper->getTableKey('main_table');
            $amount_column = $marketplaceHelper->getTableKey('amount');
            $amounts = $this->requested->getCollection()
                ->addFieldToFilter('vendor_id', ['eq' => $this->getVendorId()])
                ->addFieldToFilter(
                    'status',
                    ['eq' => \Ced\CsMarketplace\Model\Vpayment\Requested::PAYMENT_STATUS_REQUESTED]
                );

            $amounts->getSelect()->columns("SUM({$main_table}.{$amount_column}) AS amounts");

            $requestedAmount = 0.0000;
            if (count($amounts) > 0 && count($collectionTransaction) > 0) {
                $requestedAmount = $amounts->getFirstItem()->getData("amounts");
                $cancelledAmount = $collectionTransaction->addFieldToFilter(
                    'payment_state',
                    Vorders::STATE_CANCELED
                )->getData();

                if (!empty($cancelledAmount) && is_array($cancelledAmount)) {
                    foreach ($cancelledAmount as $key => $value) {
                        $requestedAmount -= $value['net_amount'];
                    }
                }
            }

            $this->setEarningAmount($this->getVendor()->getAssociatedPayments()->getFirstItem()->getBalance());
            $this->setRequestedAmount($requestedAmount);
        }
    }
}
