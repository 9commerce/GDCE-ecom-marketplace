<?php

namespace Ced\CsGst\Block\Order\Email\Items\Order;
class Bundle extends \Magento\Bundle\Block\Sales\Order\Items\Renderer
{
	public function setTemplate($template) {
		if($this->getRequest()->getActionName()=='invoice')	
			return parent::setTemplate('Ced_CsGst::order/invoice/items/renderer/bundle.phtml');
		elseif($this->getRequest()->getActionName()=='creditmemo')
			return parent::setTemplate('Ced_CsGst::order/creditmemo/items/renderer/bundle.phtml');
		elseif($this->getRequest()->getActionName()=='view')
		return parent::setTemplate('Ced_CsGst::order/items/renderer/bundle.phtml');
		elseif(($this->getRequest()->getControllerName() == 'order_invoice' && $this->getRequest()->getActionName()=='save') || ($this->getRequest()->getControllerName() == 'invoice' && $this->getRequest()->getActionName()=='save') )
		return parent::setTemplate('Ced_CsGst::email/items/invoice/bundle.phtml');
		elseif(($this->getRequest()->getControllerName() == 'order_creditmemo' && $this->getRequest()->getActionName()=='save') || ($this->getRequest()->getControllerName() == 'creditmemo' && $this->getRequest()->getActionName()=='save') )
		return parent::setTemplate('Ced_CsGst::email/items/creditmemo/bundle.phtml');
		elseif(($this->getRequest()->getControllerName() == 'order_shipment' && $this->getRequest()->getActionName()=='save') || ($this->getRequest()->getControllerName() == 'shipment' && $this->getRequest()->getActionName()=='save') )
		return parent::setTemplate($template);
		elseif(($this->getRequest()->getControllerName() == 'order' && $this->getRequest()->getActionName()=='shipment'))
		return parent::setTemplate($template);
		else 
			return parent::setTemplate('Ced_CsGst::email/items/order/bundle.phtml');
	}
}
