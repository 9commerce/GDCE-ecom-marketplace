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

use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

/**
 * Class AddCartCheck
 * @package Ced\CsPurchaseOrder\Observer
 */
class AddCartCheck implements ObserverInterface
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * AddCartCheck constructor.
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Purchaseorder $purchaseorderResource
     * @param Session $checkoutsession
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Purchaseorder $purchaseorderResource,
        Session $checkoutsession
    )
    {
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->messageManager = $messageManager;
        $this->purchaseorderResource = $purchaseorderResource;
        $this->checkoutsession = $checkoutsession;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product_id = $observer->getRequest()->getParam('product');
        $items = $this->checkoutsession->getQuote()->getAllItems();

        foreach ($items as $item) {

            $purchaseorder = $this->purchaseorderFactory->create();
            $this->purchaseorderResource->load($purchaseorder, $item->getItemId(), 'quote_item_id');

            if ($purchaseorder->getProductId() == $product_id) {
                $observer->getRequest()->setParam('product', false);
                return $this->messageManager->addErrorMessage(__('You cannot add the quote item individually in cart.'));
            }

        }
    }

}
