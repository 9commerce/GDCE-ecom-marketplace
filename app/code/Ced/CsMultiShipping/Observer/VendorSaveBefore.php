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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Observer;

use Ced\CsMultiShipping\Model\Shipping;
use Magento\Framework\Event\ObserverInterface;

class VendorSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    protected $_quoteResource;

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $csmultishippingHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;
    protected \Magento\Quote\Api\CartRepositoryInterface $quoteRepository;

    /**
     * VendorSaveBefore constructor.
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteResource = $quoteResource;
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->quoteRepository = $quoteRepository;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Adds catalog categories to top menu
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->csmultishippingHelper->isEnabled()) {
            return $this;
        }

        $vorder = $observer->getEvent()->getvorder();

        if ($vorder->getId()) {
            return $this;
        }
        $order = $vorder->getOrder();

        $quoteId = $order->getQuoteId();
        if (!$quoteId) {
            return $this;
        }
        /*$quote = $this->_quoteFactory->create();
        $this->_quoteResource->load($quote, $quoteId);*/
        $quote = $this->quoteRepository->get($quoteId);
        if ($quote && $quote->getId() && !$quote->getIsVirtual()) {
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                if ($address) {
                    $shippingMethod = $address->getShippingMethod();
                    if (substr($shippingMethod, 0, 12) == 'vendor_rates') {
                        $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
                    }
                    $shippingMethods = explode(Shipping::METHOD_SEPARATOR, $shippingMethod);
                    $vendorId = 0;
                    foreach ($shippingMethods as $method) {
                        $rate = $address->getShippingRateByCode($method);
                        $methodInfo = explode(Shipping::SEPARATOR, $method);
                        if (count($methodInfo) != 2) {
                            continue;
                        }
                        $vendorId = isset($methodInfo [1]) ? $methodInfo[1] : "admin";

                        if ($vendorId == $vorder->getVendorId()) {
                            $vorder->setShippingAmount(
                                $this->directoryHelper->currencyConvert(
                                    $rate->getPrice(),
                                    $order->getBaseCurrencyCode(),
                                    $order->getOrderCurrencyCode()
                                )
                            );
                            $vorder->setBaseShippingAmount($rate->getPrice());
                            $vorder->setCarrier($rate->getCarrier());
                            $vorder->setCarrierTitle($rate->getCarrierTitle());
                            $vorder->setMethod($rate->getMethod());
                            $vorder->setMethodTitle($rate->getMethodTitle());
                            $vorder->setCode($method);
                            $vorder->setShippingDescription(
                                $rate->getCarrierTitle() . "-" . $rate->getMethodTitle()
                            );
                            break;
                        }
                    }
                }
            }
        }
    }
}
