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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer;


use Ced\CsMarketplace\Model\Vendor;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;

/**
 * Class Approve
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer
 */
class Approve extends AbstractRenderer
{

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * Approve constructor.
     * @param Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }

    /**
     * Render approval link in each vendor row
     * @param DataObject $row
     * @return String
     */
    public function render(DataObject $row)
    {
        $html = '';

        if ($row->getEntityId() != '' && $row->getStatus() != Vendor::VENDOR_APPROVED_STATUS) {
            $url = $this->getUrl('*/*/massStatus', array('vendor_id' => $row->getEntityId(),
                'status' => Vendor::VENDOR_APPROVED_STATUS, 'inline' => 1));
            $html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\'' .
                __('Are you sure you want to Approve?') . '\', \'' . $url . '\');" >' . __('Approve') . '</a>';
        }

        if ($row->getEntityId() != '' &&
            $row->getStatus() != Vendor::VENDOR_DISAPPROVED_STATUS
        ) {
            if (strlen($html) > 0) $html .= ' | ';
            $url = $this->getUrl('*/*/massStatus', array('vendor_id' => $row->getEntityId(),
                'status' => Vendor::VENDOR_DISAPPROVED_STATUS, 'inline' => 1));
            $id = "popup-modal" . $row->getEntityId();
            $id2 = "#popup-modal" . $row->getEntityId();
            $html .= '<a class="disapprove-seller" for="' . $id2 . '" href="javascript:void(0);">' . __('Disapprove') .
                "</a>";
            $formkey = $this->formKey->getFormKey();
            $divContent = "<form style='display:none' 
                                 id='" . $id . "' 
                                 method='post' 
                                 action='" . $url . "'>
                                    <div><textarea cols='50' rows='8' name='reason'></textarea></div>
                                 <input type='hidden' name='form_key' value='" . $formkey . "'/>
                            </form>";
            $popupcontent = "<script>
            	require(
			        [
			            'jquery',
			            'Magento_Ui/js/modal/modal'
			        ],
			        function(
			            $,
			            modal
			        ) {
			            var options = {
			                type: 'popup',
			                responsive: true,
			                innerScroll: true,
			                title: 'Add your reason for disapproval',
			                buttons: [{
			                    text: $.mage.__('Continue'),
			                    class: '',
			                    click: function () {
			                         jQuery('#'+'" . $id . "').submit();
            		                 this.closeModal();
			                    }
			                }]
			            };

			            var popup = modal(options, $('#'+'" . $id . "'));
			            $('.disapprove-seller').on('click',function(){ 
            		       var openId = $(this).attr('for');
            				$(openId).modal('openModal');
			            });

			        }
			    );
			</script>";
            $html = $html . $divContent . $popupcontent;
        }

        return $html;
    }
}
