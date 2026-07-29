<?php

namespace Ced\CsPurchaseOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomPrice implements ObserverInterface
{
    /**
     * CustomPrice constructor.
     * @param Registry $registry
     */
    public function __construct(
       \Magento\Checkout\Model\Session $session
    )
    {
        $this->session = $session;
    }


    /**
     * Setting winning price on checkout page
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if($params = $this->session->getParams()) {
            $item = $observer->getEvent()->getData('quote_item');
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $price = $params['price'];

            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $this->session->unsParams();
        }
    }
}
