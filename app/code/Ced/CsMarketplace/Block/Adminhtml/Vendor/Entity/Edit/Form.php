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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Directory\Helper\Data;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Form
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit
 */
class Form extends Generic
{

    /**
     * @var Data
     */
    protected $_directoryHelper;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
    }

    /**
     * Get form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            $html = '';
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            $html .= "<script type=\"text/javascript\">" .
                "var json_regions = " . $this->_directoryHelper->getRegionJson() . ";".
                "require(['mage/adminhtml/form'], function(){" .
                "setTimeout(function(){" .
                "window.updater = new RegionUpdater('country_id',".
                "'region', 'region_id', json_regions, 'disable');".
                "}, 1000); });</script>";

            $html .= "<script>require(['jquery','jquery/ui'
								   ], function($){
				                
									   	var company_banner = $('#company_banner');
									   	company_banner.attr('accept', 'image/*');
									 	company_banner.change(function(e) {
									 		var bannerfileUpload = this;  
							 	            if (typeof (bannerfileUpload.files) != 'undefined') {
							 	                var fReader = new FileReader();
							 	                fReader.readAsDataURL(bannerfileUpload.files[0]);
							 	                fReader.onload = function (e) {
							 	                    var banner = new Image();
							 	                    banner.src = e.target.result;
							 	                    //Check Image with and height
							 	                    banner.onload = function () {
						                                        var width = this.width;
						                                        var height = this.height;
									 	                        var ratio = width/height; 
									 	                        var correctImage = width > height; 
									 	                        var minimage = width >= 1000 && height >= 300;
									 	                        var validate  = (ratio >= 3.16  && ratio <= 3.5);
									 	                        
									 	                        if (!correctImage || !minimage || !(validate)) {
									 	                        	alert(\"Minimum allowed banner dimension is 1000px X 300px and width to height ratio must be around 10:3. Current image dimension is \"+width+\"px X \"+height+\"px. \");
									 	                             
									 	                            company_banner.val(null);
									 	                            return false;
									 	                        } 
									 	                        return true;
									 	                    };

									 	                }
									 	            } else {
									 	                alert('This browser does not support HTML5.');
									 	                company_banner.val(null);
									 	                return false;
									 	            } 

									 	});
										  $( document ).ready(function() {
				
										    var country_id = document.getElementById('country_id').value;
										    var rurl ='" . $this->getUrl('*/*/country', array('_nosid' => true)) . "';
										    var formkey = '" . $this->getFormKey() . "';
										    $.ajax({
												url: rurl,
												type: 'POST',
												data: {cid:country_id,form_key:formkey},
												dataType: 'html',
												success: function(stateform) {
										    		 stateform =  JSON.parse(stateform);
													 if(stateform=='true'){
										          		 document.getElementById('region').parentNode.parentNode.style.display='none';
										          		 document.getElementById('region_id').parentNode.parentNode.style.display='block';
										        	   }else{
										          		 document.getElementById('region_id').parentNode.parentNode.style.display='none'; 
										          		 document.getElementById('region').parentNode.parentNode.style.display='block'; 
										         		}
												}
										    });
										var element = document.getElementById('region_id');
										if(element){
										  if($(element).is(':visible')){
										     element.value = '" .
                $this->_coreRegistry->registry('vendor_data')->getRegionId() . "';
									          }else{
									            setTimeout(function(){ element.value = '" .
                $this->_coreRegistry->registry('vendor_data')->getRegionId() . "';
										    }, 5000);
		                                                                    }
										  }										    
									   	 }); 

										window.onload = function() {
											var country_id = document.getElementById('country_id');
										   	country_id.onchange = function() {
											    var country_id_val = document.getElementById('country_id').value;
											    var rurl ='" . $this->getUrl('*/*/country', array('_nosid' => true)) . "';
											    $.ajax({
													url: rurl,
													type: 'POST',
													data: {cid:country_id_val},
													dataType: 'html',
													success: function(stateform) {
											    		stateform =  JSON.parse(stateform);
														 if(stateform=='true'){
											          		document.getElementById('region').parentNode.parentNode.style.display='none';
											          		document.getElementById('region_id').parentNode.parentNode.style.display='block';
											        	   }else{
											          		 document.getElementById('region_id').parentNode.parentNode.style.display='none'; 
											          		 document.getElementById('region').parentNode.parentNode.style.display='block'; 
											         		}
													}
											    });
										   	};
									   }
									   	  
								   });
								</script>";
            //phpcs:enable
            return $this->getForm()->getHtml() . $html;
        }
        return '';
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
