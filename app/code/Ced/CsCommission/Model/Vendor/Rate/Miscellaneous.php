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
 * @category  Ced
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Model\Vendor\Rate;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;

class Miscellaneous extends \Ced\CsMarketplace\Model\Vendor\Rate\Abstractrate
{
    /**
     * @var int
     */
    protected $base_fee = 0;
    /**
     * @var int
     */
    protected $fee = 0;
    /**
     * @var int|\Magento\Framework\Registry
     */
    protected $coreRegistry = 0;
    /**
     * @var int|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig = 0;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Ced\CsCommission\Helper\Category
     */
    protected $categoryHelper;
    /**
     * @var \Ced\CsCommission\Helper\Product
     */
    protected $producHelper;
    /**
     * @var \Ced\CsCommission\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $quoteItem;
    /**
     * @var \Magento\Catalog\Model\Product|\Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * Miscellaneous constructor.
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Ced\CsCommission\Helper\Category $commissionCategoryHelper
     * @param \Ced\CsCommission\Helper\Product $commissionProductHelper
     * @param \Ced\CsCommission\Helper\Data $commissionHelper
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Helper\Data $directoryHelper,
        \Ced\CsCommission\Helper\Category $commissionCategoryHelper,
        \Ced\CsCommission\Helper\Product $commissionProductHelper,
        \Ced\CsCommission\Helper\Data $commissionHelper,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $storeManager,
            $request,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->directoryHelper = $directoryHelper;
        $this->categoryHelper = $commissionCategoryHelper;
        $this->producHelper = $commissionProductHelper;
        $this->helper = $commissionHelper;
        $this->quoteItem = $quoteItem;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->product = $product;
    }

    /**
     * @param int $grand_total
     * @param int $base_grand_total
     * @param int $base_to_global_rate
     * @param array $commissionSetting
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateCommission(
        $grand_total = 0,
        $base_grand_total = 0,
        $base_to_global_rate = 1,
        $commissionSetting = []
    ) {
        try {
            $result = [];
            $order = $this->getOrder();

            $vendorId = $this->getVendorId();
            $result['base_fee'] = 0;
            $result['fee'] = 0;

            list(
                $productTypes,
                $categoryWise,
                $salesCalMethod,
                $salesRate,
                $servicetaxCalMethod,
                $servicetaxRate
                ) = $this->_getMiscellaneousConditions($vendorId);

            $itemCommission = $commissionSetting['item_commission'] ?? [];

            $customTotalPrice = 0;
            foreach ($itemCommission as $key => $itemPrice) {
                $customTotalPrice = $customTotalPrice + $itemPrice;
            }

            $salesCost = $this->helper->calculateFee($customTotalPrice, $salesRate, $salesCalMethod);
            $custom_base_fee = $salesCost;
            $custom_fee = $this->directoryHelper->currencyConvert(
                $custom_base_fee,
                $order->getBaseCurrencyCode(),
                $order->getGlobalCurrencyCode()
            );
            if (!empty($productTypes) || !empty($categoryWise)) {
                $item_commission = [];
                foreach ($order->getAllItems() as $item) {
                    if (!(isset($itemCommission[$item->getQuoteItemId()]))) {
                        continue;
                    }

                    if ($item->getVendorId() && $item->getVendorId() == $vendorId) {
                        $temp_base_fee = 0;
                        $temp_fee = 0;
                        $product_temp_priority = [];
                        $category_temp_priority = [];

                        $product = $this->product->create()->load($item->getProductId());
                        $productTypeId = (string)$product->getTypeId();

                        if (is_array($product->getCategoryIds())) {
                            $productCategoriesIds = (array)$product->getCategoryIds();
                        } else {
                            $productCategoriesIds = explode(',', trim((string)$product->getCategoryIds()));
                        }
                        $productCategoriesIds = (array)$productCategoriesIds;
                        if (isset($productTypes[$productTypeId])) {
                            $product_temp_priority = $productTypes[$productTypeId];
                        }

                        $isCategoryExist = false;
                        foreach ($categoryWise as $id => $condition) {
                            $categoryId = isset($condition['category']) &&
                            (int)$condition['category'] ? (int)$condition['category'] : 0;

                            if (!$categoryId) {
                                continue;
                            }

                            if (in_array($categoryId, $productCategoriesIds)) {
                                if (!isset($category_temp_priority['priority']) ||
                                    (isset($category_temp_priority['priority']) &&
                                        (int)$category_temp_priority['priority'] > (int)$condition['priority'])) {
                                    $category_temp_priority = $condition;
                                    $isCategoryExist = true;
                                }
                            }
                        }

                        if (!isset($category_temp_priority['priority']) && isset($categoryWise['all'])) {
                            $category_temp_priority = $categoryWise['all'];
                        }

                        /* Calculation starts for fee calculation */
                        /* START */
                        $productTypeFee = $product_temp_priority['fee'] ?? 0;
                        $categoryWiseFee = $category_temp_priority['fee'] ?? 0;
                        if ($product->getTypeId() === 'bundle') {
                            if (!empty($category_temp_priority) || !empty($product_temp_priority)) {
                                $bundleSelections = $item->getProductOptions();
                                $bundle_qty = 0;
                                foreach ($bundleSelections['bundle_options'] as $bundle_item) {
                                    $bundle_qty += $bundle_item['value'][0]['qty'];
                                }
                                if (!empty($category_temp_priority) && $category_temp_priority['method'] === 'fixed') {
                                    $categoryWiseFee *= $bundle_qty;
                                }
                                if (!empty($product_temp_priority) && $product_temp_priority['method'] === 'fixed') {
                                    $productTypeFee *= $bundle_qty;
                                }
                            }
                        }

                        $productTypeFee = $this->helper->calculateCommissionFee(
                            $itemCommission[$item->getQuoteItemId()],
                            $productTypeFee,
                            $item->getQtyOrdered(),
                            $product_temp_priority['method'] ?? $salesCalMethod
                        );
                        if ($isCategoryExist) {
                            if (isset($category_temp_priority['vendor']) &&
                                $category_temp_priority['vendor'] == $vendorId) {
                                $categoryWiseFee = $category_temp_priority['fee'] ?? 0;
                            } elseif ($category_temp_priority['vendor'] === 0 ||
                                $category_temp_priority['vendor'] == '0') {
                                $categoryWiseFee = $category_temp_priority['fee'] ?? 0;
                            }
                            $categoryWiseFee = $this->helper->calculateCommissionFee(
                                $itemCommission[$item->getQuoteItemId()],
                                $categoryWiseFee,
                                $item->getQtyOrdered(),
                                $category_temp_priority['method'] ?? $salesCalMethod
                            );
                        } else {
                            $order = $this->getOrder();
                            if ($salesCalMethod == 'percentage') {
                                $categoryWiseFee = $this->helper->calculateCommissionFee(
                                    $item->getBasePrice() * $item->getQtyOrdered(),
                                    $salesRate,
                                    $item->getQtyOrdered(),
                                    $salesCalMethod
                                );
                            } else {
                                $categoryWiseFee = $this->helper->calculateCommissionFee(
                                    $item->getBasePrice(),
                                    $salesRate,
                                    $item->getQtyOrdered(),
                                    $salesCalMethod
                                );
                            }
                        }

                        $conditionFunction = $this->scopeConfig->getValue(
                            'v' . $vendorId . '/ced_vpayments/general/commission_fn',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        );

                        if (null === $conditionFunction && !isset($conditionFunction)) {
                            $conditionFunction = $this->scopeConfig->getValue(
                                'ced_vpayments/general/commission_fn',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $order->getStoreId()
                            );
                        }

                        switch ($conditionFunction) {
                            case \Ced\CsCommission\Model\Source\Vendor\Rate\Aggregrade::TYPE_MIN:
                                $temp_base_fee = $this->getMinCommission($productTypeFee, $categoryWiseFee, $custom_base_fee);
                                $temp_fee = $this->directoryHelper->currencyConvert(
                                    $temp_base_fee,
                                    $order->getBaseCurrencyCode(),
                                    $order->getOrderCurrencyCode()
                                );
                                break;
                            case \Ced\CsCommission\Model\Source\Vendor\Rate\Aggregrade::TYPE_MAX:
                            default:
                                $temp_base_fee = $this->getMaxCommission($productTypeFee, $categoryWiseFee, $custom_base_fee);
                                $temp_fee = $this->directoryHelper->currencyConvert(
                                    $temp_base_fee,
                                    $order->getBaseCurrencyCode(),
                                    $order->getOrderCurrencyCode()
                                );
                                break;
                        }

                        /* END */
                        $quoteItem = $this->quoteItem->load($item->getQuoteItemId())->getData();

                        if (!isset($quoteItem['parent_item_id'])) {
                            $result['base_fee'] = ($result['base_fee'] + $temp_base_fee);
                            $result['fee'] = $result['fee'] + $temp_fee;
                            $item_commission[$item->getQuoteItemId()] = [
                                'base_fee' => $temp_base_fee,
                                'fee' => $temp_fee
                            ];
                        } else {
                            $parentItemId = $quoteItem['parent_item_id'];
                            $parentQuote = $this->quoteItem->load($parentItemId)->getData();
                            if ($parentQuote['product_type'] == 'bundle') {
                                $result['base_fee'] = ($result['base_fee'] + $temp_base_fee);
                                $result['fee'] = $result['fee'] + $temp_fee;
                                $item_commission[$item->getQuoteItemId()] = [
                                    'base_fee' => $temp_base_fee,
                                    'fee' => $temp_fee
                                ];
                            }
                        }
                    }
                }
                $totalBaseFeeCommisionExludeServiceTax = $result['base_fee'];
                $serviceTaxCost = $this->helper->calculateFee(
                    $totalBaseFeeCommisionExludeServiceTax,
                    $servicetaxRate,
                    $servicetaxCalMethod
                );
                $totalBaseFeeCommisionIncludeServiceTax = $totalBaseFeeCommisionExludeServiceTax + $serviceTaxCost;

                $finalCommision = min($totalBaseFeeCommisionIncludeServiceTax, $customTotalPrice);
                $result['base_fee'] = $finalCommision;
                $result['fee'] = $this->directoryHelper->currencyConvert(
                    $finalCommision,
                    $order->getBaseCurrencyCode(),
                    $order->getOrderCurrencyCode()
                );
                $result['item_commission'] = json_encode($item_commission);
            } else {
                $result['base_fee'] = $custom_base_fee;
                $result['fee'] = $custom_fee;
                /* Added separate Fixed & Percentage Files for Calculation */
                $_objectManager = ObjectManager::getInstance();
                $rate = $_objectManager->get('Ced\CsCommission\Model\Vendor\Rate\\' .
                    ucfirst($salesCalMethod));
                $rate->setOrder($order);
                $rate->setVendorId($vendorId);
                $commissionSetting['rate'] = $salesRate;
                if (is_object($rate)) {
                    $result = $rate->setOrder($order)->setVendorId($vendorId)
                        ->calculateCommission(
                            $grand_total,
                            $base_grand_total,
                            $base_to_global_rate,
                            $commissionSetting
                        );
                }
            }
            $this->coreRegistry->unregister('current_order_vendor');
            return $result;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
        }
    }

    /**
     * @param $productTypeFee
     * @param $categoryWiseFee
     * @param $custom_base_fee
     * @return mixed
     */
    protected function getMinCommission($productTypeFee, $categoryWiseFee, $custom_base_fee){
        if ($productTypeFee && $categoryWiseFee) {
            $temp_base_fee = min($productTypeFee, $categoryWiseFee);
        } else if ($categoryWiseFee) {
            $temp_base_fee = $categoryWiseFee;
        } else {
            $temp_base_fee = $productTypeFee;
        }

        if (!$temp_base_fee) {
            $temp_base_fee = $custom_base_fee;
        }

        return $temp_base_fee;
    }

    /**
     * @param $productTypeFee
     * @param $categoryWiseFee
     * @param $custom_base_fee
     * @return mixed
     */
    protected function getMaxCommission($productTypeFee, $categoryWiseFee, $custom_base_fee){
        if ($productTypeFee && $categoryWiseFee) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug("if");
            $temp_base_fee = max($productTypeFee, $categoryWiseFee);
        } else {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug("categorywisefee:".$categoryWiseFee);
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug("categorywisefee:".$productTypeFee);
            if ($categoryWiseFee) {
                $temp_base_fee = $categoryWiseFee;
            } else {
                $temp_base_fee = $productTypeFee;
            }
        }
        if (!$temp_base_fee) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug("custom_base_fee".$custom_base_fee);
            $temp_base_fee = $custom_base_fee;
        }
        return $temp_base_fee;
    }

    /**
     * @param $vendorId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getMiscellaneousConditions($vendorId)
    {
        if ($this->coreRegistry->registry('current_order_vendor')) {
            $this->coreRegistry->unregister('current_order_vendor');
        }
        $order = $this->getOrder();

        $this->coreRegistry->register('current_order_vendor', $vendorId);
        $categoryWise = $this->categoryHelper->getUnserializedOptions($vendorId, $order->getStoreId());
        $productTypes = $this->producHelper->getUnserializedOptions($vendorId, $order->getStoreId());
        //Customize code to get sales, ship, payments & service tax
        if ($vendorId != null) {
            $salesCalMethod = $this->scopeConfig->getValue(
                'v' . $vendorId . '/ced_vpayments/general/commission_mode_default'
            );
            if ($salesCalMethod == null) {
                $salesCalMethod = $this->scopeConfig->getValue(
                    'ced_vpayments/general/commission_mode_default',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                );
            }
            $salesRate = $this->scopeConfig->getValue(
                'v' . $vendorId . '/ced_vpayments/general/commission_fee_default'
            );
            if ($salesRate == null) {
                $salesRate = $this->scopeConfig->getValue(
                    'ced_vpayments/general/commission_fee_default',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                );
            }
        } else {
            $salesCalMethod = $this->scopeConfig->getValue(
                'ced_vpayments/general/commission_mode_default',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );
            $salesRate = $this->scopeConfig->getValue(
                'ced_vpayments/general/commission_fee_default',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );
        }
        $shipCalMethod = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_mode_ship',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $shipRate = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_fee_ship',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $paymentCalMethod = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_mode_payments',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $paymentRate = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_fee_paymnets',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $servicetaxCalMethod = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_mode_servicetax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );
        $servicetaxRate = $this->scopeConfig->getValue(
            'ced_vpayments/general/commission_fee_servicetax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        return [
            $productTypes,
            $categoryWise,
            $salesCalMethod,
            $salesRate,
            $shipCalMethod,
            $shipRate,
            $paymentCalMethod,
            $paymentRate,
            $servicetaxCalMethod,
            $servicetaxRate
        ];
    }
}
