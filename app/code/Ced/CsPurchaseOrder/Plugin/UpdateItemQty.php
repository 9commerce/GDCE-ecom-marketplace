<?php

namespace Ced\CsPurchaseOrder\Plugin;

use Magento\Checkout\Controller\Sidebar\UpdateItemQty as coreUpdateItemQty;
use Magento\Framework\Json\Helper\Data as coreData;
use Magento\Checkout\Model\Sidebar;
use Magento\Checkout\Model\Cart;
use Ced\CsPurchaseOrder\Model\PurchaseorderFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;

/**
 * Class UpdateItemQty
 * @package Ced\CsPurchaseOrder\Plugin
 */
class UpdateItemQty
{
    /**
     * @var coreData
     */
    protected $jsonHelper;

    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * UpdateItemQty constructor.
     * @param coreData $jsonHelper
     * @param Sidebar $sidebar
     * @param Cart $cart
     * @param PurchaseorderFactory $purchaseorderFactory
     * @param Purchaseorder $purchaseorderResource
     */
    public function __construct(
        coreData $jsonHelper,
        Sidebar $sidebar,
        Cart $cart,
        PurchaseorderFactory $purchaseorderFactory,
        Purchaseorder $purchaseorderResource
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->sidebar = $sidebar;
        $this->cart = $cart;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->purchaseorderResource = $purchaseorderResource;
    }

    /**
     * @param coreUpdateItemQty $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(coreUpdateItemQty $subject, \Closure $proceed)
    {
        try {
            $items = $this->cart->getItems();
            if ($items) {
                $purchaseorder = $this->purchaseorderFactory->create();

                foreach ($items as $item) {

                    $this->purchaseorderResource->load($purchaseorder, $item->getItemId(), 'quote_item_id');
                    $productid = $purchaseorder->getProductId();

                    if ($productid == $item->getProductId()) {
                        $errorMsg = 'You cannot update the quantity of the quote item';
                        return $subject->getResponse()->representJson(
                            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($errorMsg))
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            return $subject->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($e->getMessage()))
            );
        }
        return $proceed();

    }
}