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
 * Class Percentage
 * @package Ced\CsMarketplace\Model\Vendor\Rate
 */
class Percentage extends Abstractrate
{

    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected $extensionAttributes;

    /**
     * @var AttributeValueFactory
     */
    protected $customAttributeFactory;

    /**
     * @var string[]
     */
    protected $customAttributesCodes = null;

    /**
     * @var bool
     */
    protected $customAttributesChanged = false;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Percentage constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
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
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
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

        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get the commission based on group
     * @param int $grand_total
     * @param int $base_grand_total
     * @param int $base_to_global_rate
     * @param array $commissionSetting
     * @return array
     */
    public function calculateCommission(
        $grand_total = 0,
        $base_grand_total = 0,
        $base_to_global_rate = 1,
        $commissionSetting = []
    ) {
        $result = [];

        $order = $this->getOrder();
        $commissionSetting['rate'] = min($commissionSetting['rate'], 100);
        $base_fee = ($commissionSetting['rate'] * $base_grand_total) / 100;
        $result['base_fee'] = max($base_fee, 0);
        $fee = (floatval($commissionSetting['rate']) * floatval($grand_total)) / 100;
        $result['fee'] = max($fee, 0);

        $itemCommission = isset($commissionSetting['item_commission']) ?
            $commissionSetting['item_commission'] : [];
        if (count($itemCommission) > 0) {
            unset($commissionSetting['item_commission']);
            $item_commission = [];
            foreach ($itemCommission as $itemId => $base_price) {
                $price = $this->priceCurrency->format(
                    $order->getGlobalCurrencyCode(),
                    false,
                    2,
                    null,
                    $order->getCurrency()
                );

                $item_commission[$itemId] = $this->calculateCommission($price,
                    $base_price,
                    $base_to_global_rate,
                    $commissionSetting);
            }
            $result['item_commission'] = json_encode($item_commission);
        }

        return $result;
    }
}
