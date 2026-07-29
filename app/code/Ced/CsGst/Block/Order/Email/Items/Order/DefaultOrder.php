<?php
namespace Ced\CsGst\Block\Order\Email\Items\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
	public function setTemplate($template) 
	{  
		return parent::setTemplate('Ced_CsGst::email/items/order/default.phtml');
	}

	 /**
     * Get the html for item price
     *
     * @param OrderItem $item
     * @return string
     */
    public function getItemPrice(OrderItem $item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}