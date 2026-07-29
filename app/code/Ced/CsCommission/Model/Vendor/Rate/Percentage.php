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

use Magento\Framework\Api\AttributeValueFactory;
use Ced\CsMarketplace\Model\Vendor\Rate\Abstractrate;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Percentage extends Abstractrate
{
    /**
     * @var ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;

    /**
     * @var ExtensionAttributesInterface
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
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Percentage constructor.
     * @param PriceCurrencyInterface $priceCurrency
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
        PriceCurrencyInterface $priceCurrency,
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

        $itemCommission = $commissionSetting['item_commission'] ?? [];
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

                $item_commission[$itemId] = $this->calculateCommission(
                    $price,
                    $base_price,
                    $base_to_global_rate,
                    $commissionSetting
                );
            }
            $result['item_commission'] = json_encode($item_commission);
        }
        return $result;
    }
}
