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

namespace Ced\CsCommission\Model\Vendor\Rate;

use Ced\CsMarketplace\Model\Vendor\Rate\Abstractrate;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;

class Fixed extends Abstractrate
{

    /**
     * @var ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * Fixed constructor.
     * @param ItemFactory $quoteItemFactory
     * @param Data $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ItemFactory $quoteItemFactory,
        Data $directoryHelper,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->quoteItemFactory = $quoteItemFactory;
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
        $order = $this->getOrder();
        $result = [];
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
        $itemCommission = $commissionSetting['item_commission'] ?? [];
        if (count($itemCommission) > 0) {
            unset($commissionSetting['item_commission']);
            $item_commission = [];
            foreach ($itemCommission as $itemId => $base_price) {
                $qty = (int)$this->quoteItemFactory->create()->load($itemId)->getQty();
                $price = $this->directoryHelper->currencyConvert(
                    $base_price,
                    $order->getBaseCurrencyCode(),
                    $order->getGlobalCurrencyCode()
                );
                $item_commission[$itemId] =
                    $this->calculateCommission($price, $base_price, $base_to_global_rate, $commissionSetting, $qty);
            }

            $result['item_commission'] = json_encode($item_commission);
            foreach ($item_commission as $commission) {
                $fee += $commission['fee'];
            }
            $result['fee'] = $fee;
            $result['base_fee'] = $fee;
        }
        return $result;
    }
}
