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

namespace Ced\CsMultiShipping\Model\Cart;

class ShippingMethodConverter extends \Magento\Quote\Model\Cart\ShippingMethodConverter
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor
     */
    protected $_vendorResource;

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $csmultishippingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ShippingMethodConverter constructor.
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Helper\Data $taxHelper
     */
    public function __construct(
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource,
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_vendorFactory = $vendorFactory;
        $this->_vendorResource = $vendorResource;
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->storeManager = $storeManager;
        parent::__construct($shippingMethodDataFactory, $storeManager, $taxHelper);
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param string $quoteCurrencyCode
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        /**
         * @var \Magento\Directory\Model\Currency $currency
         */
        if (!$this->csmultishippingHelper->isEnabled()) {
            return parent::modelToDataObject($rateModel, $quoteCurrencyCode);
        }
        $store = $this->storeManager->getStore();
        $currency = $store->getBaseCurrency();
        $errorMessage = $rateModel->getErrorMessage();
        $vendorId = 'admin';
        if ($rateModel->getCarrier() != 'vendor_rates') {
            $tmp = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $rateModel->getCode());
            $vendorId = isset($tmp[1]) ? $tmp[1] : "admin";
        }
        $vendor = $this->_vendorFactory->create();
        if ($vendorId && $vendorId != "admin") {
            $this->_vendorResource->load($vendor, $vendorId);
        }
        $carrier_title = $this->csmarketplaceHelper
            ->getStoreConfig('ced_csmultishipping/general/carrier_title', $store->getId());

        $title = $vendor->getId() ? $vendor->getPublicName() : $carrier_title;

        return $this->shippingMethodDataFactory->create()
            ->setCarrierCode($rateModel->getCarrier())
            ->setMethodCode($rateModel->getMethod())
            ->setCarrierTitle($title)
            ->setMethodTitle($rateModel->getMethodTitle())
            ->setAmount($currency->convert($rateModel->getPrice(), $quoteCurrencyCode))
            ->setBaseAmount($rateModel->getPrice())
            ->setAvailable(empty($errorMessage))
            ->setErrorMessage(empty($errorMessage) ? false : $errorMessage)
            ->setPriceExclTax(
                $currency->convert($this->getShippingPriceWithFlag($rateModel, false), $quoteCurrencyCode)
            )
            ->setPriceInclTax(
                $currency->convert($this->getShippingPriceWithFlag($rateModel, true), $quoteCurrencyCode)
            );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param bool $flag
     * @return float
     */
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }
}
