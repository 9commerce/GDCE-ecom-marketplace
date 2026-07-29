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

namespace Ced\CsMultiShipping\Model;

use Magento\Shipping\Model\Config;

class Shipping extends \Magento\Shipping\Model\Shipping
{
    const SEPARATOR = '~';
    const METHOD_SEPARATOR = ':';
    const VID_FOR_ADMIN = 'admin';

    /**
     * @var \Ced\CsMultiShipping\Helper\Data
     */
    protected $csmultishippingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor
     */
    protected $_vendorResource;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResource;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region
     */
    protected $_regionResource;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * Shipping constructor.
     * @param \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region $regionResource
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Ced\CsMultiShipping\Helper\Data $csmultishippingHelper,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\ResourceModel\Region $regionResource,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        parent::__construct(
            $scopeConfig,
            $shippingConfig,
            $storeManager,
            $carrierFactory,
            $rateResultFactory,
            $shipmentRequestFactory,
            $regionFactory,
            $mathDivision,
            $stockRegistry
        );
        $this->_regionResource = $regionResource;
        $this->csmultishippingHelper = $csmultishippingHelper;
        $this->registry = $registry;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->_vendorResource = $vendorResource;
        $this->productFactory = $productFactory;
        $this->_productResource = $productResource;
        $this->checkoutSession = $checkoutSession;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->_rateMethodFactory = $rateMethodFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return $this|\Magento\Shipping\Model\Shipping
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->csmultishippingHelper->isEnabled()) {
            return parent::collectRates($request);
        }
        $quotes = [];
        $vendorActiveMethods = [];
        $vendorAddressDetails = [];
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            if ($vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($item->getProduct())) {
                $vendor = $this->vendorFactory->create();
                $this->_vendorResource->load($vendor, $vendorId);
                if ($vendor && $vendor->getId()) {
                    if ($this->registry->registry('current_order_vendor')){
                        $this->registry->unRegister('current_order_vendor');
                    }
                    $this->registry->register('current_order_vendor', $vendor);
                }
                if (isset($vendorActiveMethods[$vendorId]) && count($vendorActiveMethods[$vendorId]) > 0
                    && isset($vendorAddressDetails[$vendorId]) && count($vendorAddressDetails[$vendorId]) > 0
                ) {
                    $activeMethods = $vendorActiveMethods[$vendorId];
                    $vendorAddress = $vendorAddressDetails[$vendorId];
                } else {
                    $activeMethods = $this->csmultishippingHelper->getActiveVendorMethods($vendorId);
                    $vendorAddress = $this->csmultishippingHelper->getVendorAddress($vendorId);
                }
                if (count($activeMethods) > 0 && $this->csmultishippingHelper->validateAddress($vendorAddress)
                    && $this->csmultishippingHelper->validateSpecificMethods($activeMethods)
                ) {
                    if (!isset($quotes[$vendorId])) {
                        $quotes[$vendorId] = [];
                    }
                    $quotes[$vendorId][] = $item;
                    if (!isset($vendorActiveMethods[$vendorId])) {
                        $vendorActiveMethods[$vendorId] = $activeMethods;
                    }
                    if (!isset($vendorAddressDetails[$vendorId])) {
                        $vendorAddressDetails[$vendorId] = $vendorAddress;
                    }
                } else {
                    $quotes[self::VID_FOR_ADMIN][] = $item;
                }
                if ($this->registry->registry('current_order_vendor') != null) {
                    $this->registry->unregister('current_order_vendor');
                }
            } else {
                $quotes[self::VID_FOR_ADMIN][] = $item;
            }
        }
        if ($this->registry->registry('current_order_vendor') != null) {
            $this->registry->unregister('current_order_vendor');
        }
        $origRequest = clone $request;
        $last_count = 0;
        $prod_model = $this->productFactory->create();
        if ($this->checkoutSession->getInvalidItem()) {
            $this->checkoutSession->unsInvalidItem();
        }

        foreach ($quotes as $vendorId => $items) {
            $request = clone $origRequest;
            $request->setVendorId($vendorId);
            $request->setVendorItems($items);
            $request->setAllItems($items);
            $request->setPackageWeight($this->getItemWeight($request, $items));
            $request->setPackageQty($this->getItemQty($request, $items));
            $request->setPackageValue($this->getItemSubtotal($request, $items));
            $request->setBaseSubtotalInclTax($this->getItemSubtotal($request, $items));

            if ($vendorId != self::VID_FOR_ADMIN) {
                $vendor = $this->vendorFactory->create();
                $this->_vendorResource->load($vendor, $vendorId);
                if ($vendor && $vendor->getId()) {
                    $this->registry->register('current_order_vendor', $vendor);
                }
                $vendorCarriers = array_keys($vendorActiveMethods[$vendorId]);
                $vendorAddress = $vendorAddressDetails[$vendorId];
                if (isset($vendorAddress['country_id'])) {
                    $request->setOrigCountry($vendorAddress['country_id']);
                }
                if (isset($vendorAddress['region'])) {
                    $request->setOrigRegionCode($vendorAddress['region']);
                }
                if (isset($vendorAddress['region_id'])) {
                    $origRegionCode = $vendorAddress['region_id'];
                    if (is_numeric($origRegionCode)) {
                        $regionModel = $this->_regionFactory->create();
                        $this->_regionResource->load($regionModel, $origRegionCode);
                        $origRegionCode = $regionModel->getCode();
                    }
                    $request->setOrigRegionCode($origRegionCode);
                }
                if (isset($vendorAddress['postcode'])) {
                    $request->setOrigPostcode($vendorAddress['postcode']);
                }
                if (isset($vendorAddress['city'])) {
                    $request->setOrigCity($vendorAddress['city']);
                }
            }

            $storeId = $request->getStoreId();
            if (!$request->getOrig()) {
                $request->setCountryId(
                    $this->csmarketplaceHelper->getStoreConfig(Config::XML_PATH_ORIGIN_COUNTRY_ID, $storeId)
                )->setRegionId(
                    $this->csmarketplaceHelper->getStoreConfig(Config::XML_PATH_ORIGIN_REGION_ID, $storeId)
                )->setCity(
                    $this->csmarketplaceHelper->getStoreConfig(Config::XML_PATH_ORIGIN_CITY, $storeId)
                )->setPostcode(
                    $this->csmarketplaceHelper->getStoreConfig(Config::XML_PATH_ORIGIN_POSTCODE, $storeId)
                );
            }

            $limitCarrier = $request->getLimitCarrier();
            if (!is_array($limitCarrier)) {
                if ($limitCarrier == 'vendor' || $limitCarrier == 'vendor_rates') {
                    $limitCarrier = '';
                }
            }
            if (!$limitCarrier) {
                $carriers = $this->csmarketplaceHelper->getStoreConfig('carriers', $storeId);
                foreach ($carriers as $carrierCode => $carrierConfig) {
                    if ($vendorId != self::VID_FOR_ADMIN) {
                        if (!in_array($carrierCode, $vendorCarriers)) {
                            continue;
                        }
                        $request->setVendorShippingSpecifics($vendorActiveMethods[$vendorId][$carrierCode]);
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }
            } else {
                if (!is_array($limitCarrier)) {
                    $limitCarrier = [$limitCarrier];
                }
                foreach ($limitCarrier as $carrierCode) {
                    if ($vendorId != self::VID_FOR_ADMIN) {
                        if (!in_array($carrierCode, $vendorCarriers)) {
                            continue;
                        }
                    }
                    $carrierConfig = $this->csmarketplaceHelper->getStoreConfig('carriers/' . $carrierCode, $storeId);
                    if (!$carrierConfig) {
                        continue;
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }
            }
            if ($this->registry->registry('current_order_vendor') != null) {
                $this->registry->unregister('current_order_vendor');
            }

            $total_count = count($this->getResult()->getAllRates());
            $current_count = $total_count - $last_count;
            $last_count = $total_count;
            if ($current_count < 1) {
                $prod_name = [];
                $prod_name = $this->checkoutSession->getInvalidItem();
                foreach ($items as $item) {
                    $this->_productResource->load($prod_model, $item->getProductId());
                    $prod_name[] = $prod_model->getName();
                }
                $this->checkoutSession->setInvalidItem($prod_name);
            }
        }

        $shippingRates = $this->getResult()->getAllRates();
        $newVendorRates = [];
        $methodTitles = [];
        $hasOnlyAdminProducts = true;
        foreach ($this->ratesByVendor($shippingRates) as $vendorId => $rates) {
            /*@note: if there are any vendor product then variable will be assigned to false*/
            $hasOnlyAdminProducts = ($hasOnlyAdminProducts && $vendorId == self::VID_FOR_ADMIN);

            if (!count($newVendorRates)) {
                foreach ($rates as $rate) {
                    $methodCode = $rate->getCarrier() . '_' . $rate->getMethod();
                    $newVendorRates[$methodCode] = $rate->getPrice();
                    $methodTitles[$methodCode] = [
                        'carrier_title' => $rate->getCarrierTitle(),
                        'method_title' => $rate->getMethodTitle(),
                    ];
                }
            } else {
                $tmpRates = [];
                foreach ($rates as $rate) {
                    $methodCode = $rate->getCarrier() . '_' . $rate->getMethod();
                    $methodTitles[$methodCode] = [
                        'carrier_title' => $rate->getCarrierTitle(),
                        'method_title' => $rate->getMethodTitle(),
                    ];
                    foreach ($newVendorRates as $code => $shippingPrice) {
                        $tmpRates[$code . self::METHOD_SEPARATOR . $methodCode] = $shippingPrice + $rate->getPrice();
                    }
                }
                $newVendorRates = $tmpRates;
            }
        }
        $marketplaceMethodTitle = $this->csmarketplaceHelper->getStoreConfig(
            'ced_csmultishipping/general/method_title',
            $storeId
        );
        $marketplaceCarrierTitle = $this->csmarketplaceHelper->getStoreConfig(
            'ced_csmultishipping/general/carrier_title',
            $storeId
        );

        foreach ($newVendorRates as $code => $shippingPrice) {
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier('vendor_rates');
            if($hasOnlyAdminProducts && isset($methodTitles[$code])){
                $method->setCarrierTitle($methodTitles[$code]['carrier_title']);
                $method->setMethodTitle($methodTitles[$code]['method_title']);
            }else{
                $method->setCarrierTitle(__($marketplaceCarrierTitle));
                $method->setMethodTitle(__($marketplaceMethodTitle));
            }

            $method->setMethod($code);
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $this->getResult()->append($method);
        }
        return $this;
    }

    /**
     * Retrieve item quantity by id
     * @param $request
     * @param $items
     * @return float|int
     */
    public function getItemWeight($request, $items)
    {
        $qty = 0;
        foreach ($items as $item) {
            if ($item->getTypeId() == 'virtual') {
                continue;
            }
            $qty += $item->getQty() * $item->getWeight();
        }
        return $qty;
    }

    /**
     * Retrieve item quantity by id
     * @param $request
     * @param $items
     * @return int
     */
    public function getItemQty($request, $items)
    {
        $qty = 0;
        foreach ($items as $item) {
            if ($item->getTypeId() == 'virtual') {
                continue;
            }
            $qty += $item->getQty();
        }
        return $qty;
    }

    /**
     * Retrieve item Base subtotal by id
     * @param $request
     * @param $items
     * @return int
     */
    public function getItemSubtotal($request, $items)
    {
        $row_total = 0;
        foreach ($items as $item) {
            $row_total += $item->getBaseRowTotalInclTax();
        }
        return $row_total;
    }

    /**
     * Group shipping rates by each vendor.
     * @param unknown $shippingRates
     */
    public function ratesByVendor($shippingRates)
    {
        $rates = [];
        foreach ($shippingRates as $rate) {
            if (!$rate->getVendorId()) {
                $rate->setVendorId(self::VID_FOR_ADMIN);
            }
            if (!isset($rates[$rate->getVendorId()])) {
                $rates[$rate->getVendorId()] = [];
            }
            $rates[$rate->getVendorId()][] = $rate;
        }
        ksort($rates);
        return $rates;
    }
}
