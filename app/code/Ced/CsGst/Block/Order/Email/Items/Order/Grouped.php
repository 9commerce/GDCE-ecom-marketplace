<?php
namespace Ced\CsGst\Block\Order\Email\Items\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
class Grouped extends \Magento\GroupedProduct\Block\Order\Email\Items\Order\Grouped
{
	public function setTemplate($template) {
		return parent::setTemplate('Ced_CsGst::email/items/order/default.phtml');
	}
}