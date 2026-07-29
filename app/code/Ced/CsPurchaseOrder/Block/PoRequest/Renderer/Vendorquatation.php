<?php
namespace Ced\CsPurchaseOrder\Block\PoRequest\Renderer;

class Vendorquatation extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
       \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
      
       protected $_template = 'Ced_CsPurchaseOrder::renderer/form/vendorquantityfield.phtml';
       
       public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
       {
       		   // die('rjhdbf');
               $this->_element = $element;
               $html = $this->toHtml();
               return $html;
       }
}