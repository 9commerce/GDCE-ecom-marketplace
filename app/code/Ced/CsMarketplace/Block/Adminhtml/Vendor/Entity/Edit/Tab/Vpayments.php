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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab;

/**
 * Class Vpayments
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab
 */
class Vpayments extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Vpayments constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Helper\Payment $paymentHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Helper\Payment $paymentHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->priceCurrency = $priceCurrency;
        //$this->_status = $status;
        parent::__construct($context, $data);

        $this->setVendor($coreRegistry->registry('vendor_data'));
        $this->setPendingAmount(0.00);
        $this->setPendingTransfers(0);
        $this->setPaidAmount(0.00);
        $this->setCanceledAmount(0.00);
        $this->setRefundableAmount(0.00);
        $this->setRefundedAmount(0.00);
        $this->setEarningAmount(0.00);

        if ($this->getVendor() && $this->getVendor()->getId()) {
            $collection = $this->getVendor()->getAssociatedOrders();
            $collection->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns('payment_state')
                ->columns('COUNT(*) as count')
                ->columns('SUM(order_total) as order_total')
                ->columns('(SUM(order_total) - SUM(shop_commission_fee)) AS net_amount')
                ->group("payment_state");

            if (!empty($collection)) {
                foreach ($collection as $stats) {
                    switch ($stats->getPaymentState()) {
                        case \Ced\CsMarketplace\Model\Vorders::STATE_OPEN :
                            $this->setPendingAmount($stats->getNetAmount());
                            $this->setPendingTransfers($stats->getCount() ? $stats->getCount() : 0);
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_PAID :
                            $this->setPaidAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_REFUND :
                            $this->setRefundableAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_REFUNDED :
                            $this->setRefundedAmount($stats->getNetAmount());
                            break;
                        case \Ced\CsMarketplace\Model\Vorders::STATE_CANCELED :
                            $this->setCanceledAmount($stats->getNetAmount());
                            break;
                    }
                }
            }
            $this->setEarningAmount($this->getVendor()->getAssociatedPayments()->getFirstItem()->getBalance());
        }

        $this->setTemplate('vendor/entity/edit/tab/vpayments.phtml');
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * @return \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public function getPriceCurrency()
    {
        return $this->priceCurrency;
    }

    /**
     * @return $this|\Magento\Backend\Block\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $grid = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid', 'vpayments.grid');
        $this->setChild('vpayments.grid', $grid);
        return $this;
    }
}
