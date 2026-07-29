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

namespace Ced\CsMarketplace\Plugin;

use Ced\CsMarketplace\Model\Vproducts;
use Closure;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;


/**
 * Class SetVendorIdInOrderItem
 * @package Ced\CsMarketplace\Plugin
 */
class SetVendorIdInOrderItem
{

    /**
     * @var Vproducts
     */
    protected $vproducts;

    /**
     * SetVendorIdInOrderItem constructor.
     * @param Vproducts $vproducts
     */
    public function __construct(Vproducts $vproducts)
    {
        $this->vproducts = $vproducts;
    }

    /**
     * @param ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return Item
     * @throws LocalizedException
     */
    public function aroundConvert(
        ToOrderItem $subject,
        Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        /** @var Item $orderItem */
        $orderItem = $proceed($item, $additional);

        if (!$item->getVendorId()) {
            $productId = $item->getProductId();
            $id = $this->vproducts->loadByField('product_id', $productId)->getVendorId();
            $orderItem->setVendorId($id);
            return $orderItem;
        }
        $orderItem->setVendorId($item->getVendorId());
        return $orderItem;
    }
}
