<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsPurchaseOrder
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsPurchaseOrder\Block\Vendor\Edit;
 
/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    { 
        parent::_construct();
        $this->setId('grid_records');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Quotations List'));
        $this->setData('area','adminhtml');
    }


    protected function _beforeToHtml()
    {
        
        $this->addTab(
            'quote_details',
            [
                'label' => __('Quotations Details'),
                'title' => __('Quotations Details'),
                'content' => //$this->getLayout()->createBlock('Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab\Qlist')->toHtml()
        		$this->getLayout()->createBlock('Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab\Qlist')->setTemplate('Ced_CsPurchaseOrder::purchaseorder/main.phtml')
        		->toHtml(),

]
        );
        
        $this->addTab(
        		'quote_send',
        		[
        		'label' => __('Send Your Quotations'),
        		'title' => __('Quotations Details'),
        		'content' => $this->getLayout()->createBlock('Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab\Vquotation')->toHtml()
        		]
        );
        
        

         return parent::_beforeToHtml();
     }
}