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

namespace Ced\CsMarketplace\Model\Vendor\Rate;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class Fixed
 * @package Ced\CsMarketplace\Model\Vendor\Rate
 */
class Fixed extends Abstractrate
{

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * Fixed constructor.
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
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
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
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

        $this->quoteItemFactory = $quoteItemFactory;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Get the commission based on group
     * @param int $grand_total
     * @param int $base_grand_total
     * @param int $base_to_global_rate
     * @param array $commissionSetting
     * @param int $qty
     * @return array
     */
    public function calculateCommission(
        $grand_total = 0,
        $base_grand_total = 0,
        $base_to_global_rate = 1,
        $commissionSetting = [],
        $qty = 0
    ) {
        $result = [];

        $order = $this->getOrder();
        $fee = 0;

        $result['base_fee'] = min($base_grand_total, $commissionSetting['rate']) * $qty;
        $result['fee'] = min(
                $grand_total,
                $this->directoryHelper->currencyConvert(
                    $commissionSetting['rate'],
                    $order->getBaseCurrencyCode(),
                    $order->getGlobalCurrencyCode()
                )
            ) * $qty;


        $itemCommission = isset($commissionSetting['item_commission']) ? $commissionSetting['item_commission'] : [];
        if (count($itemCommission) > 0) {
            unset($commissionSetting['item_commission']);
            $item_commission = [];

            foreach ($itemCommission as $itemId => $base_price) {
                $qty = (int)$this->quoteItemFactory->create()->load($itemId)->getQty();
                $price = $this->directoryHelper->currencyConvert($base_price, $order->getBaseCurrencyCode(),
                    $order->getGlobalCurrencyCode());
                $item_commission[$itemId] =
                    $this->calculateCommission($price, $base_price, $base_to_global_rate, $commissionSetting, $qty);
            }

            $result['item_commission'] = json_encode($item_commission);
            foreach ($item_commission as $commission) {
                $fee += $commission['fee'];
            }
            $result['base_fee'] = $fee;
            $result['fee'] = $fee;
        }

        return $result;
    }
}
