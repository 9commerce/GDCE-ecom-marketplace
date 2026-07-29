<?php
namespace Ced\CsGst\Block\Order\Email\Items\Order;
class Downloadable extends \Magento\Downloadable\Block\Sales\Order\Email\Items\Order\Downloadable
{
	public function setTemplate($template) {
		return parent::setTemplate('Ced_CsGst::email/items/order/downloadble.phtml');
	}
}