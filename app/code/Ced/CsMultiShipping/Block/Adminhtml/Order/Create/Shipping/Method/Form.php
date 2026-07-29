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

namespace Ced\CsMultiShipping\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Helper\SecureHtmlRender\TagData;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRender\HtmlRenderer;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    protected \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper;
    protected \Ced\CsMarketplace\Model\VendorFactory $_vendorFactory;
    protected \Ced\CsMarketplace\Model\ResourceModel\Vendor $_vendorResource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource,
        Random $random,
        HtmlRenderer $renderer,
        $processors = [],
        array $data = []
    )
    {
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->_vendorFactory = $vendorFactory;
        $this->_vendorResource = $vendorResource;
        $this->random = $random;
        $this->processors = $processors;
        $this->renderer = $renderer;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
    }

    /**
     * @return array
     */
    public function getRatesByVendor()
    {
        $addrs_mthd = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
        $groups = [];

        foreach ($addrs_mthd as $code => $rateCollection) {
            foreach ($rateCollection as $rate) {
                if ($rate->isDeleted()) {
                    continue;
                }
                if ($rate->getCarrier() == 'vendor_rates') {
                    continue;
                }

                $tmp = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $rate->getCode());

                $vendorId = isset($tmp[1]) ? $tmp[1] : "admin";
                $vendor = $this->_vendorFactory->create();
                if ($vendorId && $vendorId != "admin") {
                    $this->_vendorResource->load($vendor, $vendorId);
                }

                if (!isset($groups[$vendorId])) {
                    $groups[$vendorId] = [];
                }

                $groups[$vendorId]['title'] = $vendor->getId() ?
                    $vendor->getPublicName() : "Admin";

                if (!isset($groups[$vendorId]['rates'])) {
                    $groups[$vendorId]['rates'] = [];
                }
                $groups[$vendorId]['rates'][] = $rate;
            }
        }
        return $groups;
    }

    /**
     * @param $address
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVendorShippingRates($address)
    {
        if (!$this->csmultishippingHelper->isEnabled()) {
            return $this->getShippingRates();
        }
        $groups = $address->getGroupedAllShippingRates();

        $rates = [];
        foreach ($groups as $code => $_rates) {
            if ($code == 'vendor_rates') {
                foreach ($_rates as $rate) {
                    if (!$rate->isDeleted()) {
                        if (!isset($rates[$rate->getCarrier()])) {
                            $rates[$rate->getCarrier()] = [];
                        }
                        $rates[$rate->getCarrier()][] = $rate;
                    }
                }
            }
        }
        return $rates;
    }

    /**
     * @param $address
     * @return false|string[]
     */
    public function getSelectedMethod($address)
    {
        $selectedMethod = str_replace("vendor_rates_", '', $address->getShippingMethod() ?? '');
        $selectedMethods = explode(\Ced\CsMultiShipping\Model\Shipping::METHOD_SEPARATOR, $selectedMethod);
        return $selectedMethods;
    }

    /**
     * Retrieves the address shipping method
     *
     * @param Address $address
     * @return mixed
     */
    public function getAddressShippingMethod($address)
    {
        return $address->getShippingMethod();
    }
}
