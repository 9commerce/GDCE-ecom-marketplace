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
 * @package     Ced_CsPurchaseOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;

/**
 * Class PlaceOrderAfter
 * @package Ced\CsPurchaseOrder\Observer
 */
class PlaceOrderAfter implements ObserverInterface
{
    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var Session
     */
    protected $checkoutsession;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * PlaceOrderAfter constructor.
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param Session $checkoutsession
     * @param Purchaseorder $purchaseorderResource
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        Session $checkoutsession,
        Purchaseorder $purchaseorderResource
    )
    {
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->checkoutsession = $checkoutsession;
        $this->purchaseorderResource = $purchaseorderResource;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $items = $this->checkoutsession->getQuote()->getAllItems();

        if ($items) {
            try {
                foreach ($items as $item) {

                    $purchaseorder = $this->purchaseorderFactory->create();
                    $this->purchaseorderResource->load($purchaseorder, $item->getItemId(), 'quote_item_id');

                    if ($purchaseorder->getData()) {
                        $purchaseorder->setOrderId($order->getIncrementId())
                            ->setStatus('orderplaced');
                        $this->purchaseorderResource->save($purchaseorder);
                        $this->checkoutsession->unsParams();
                    }
                }
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
    }

}
