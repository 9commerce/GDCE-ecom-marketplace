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

namespace Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer;


use Ced\CsMarketplace\Model\Vproducts;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;

/**
 * Class Action
 * @package Ced\CsMarketplace\Block\Adminhtml\Vproducts\Renderer
 */
class Action extends AbstractRenderer
{

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * Action constructor.
     * @param Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $id = "popup-modal" . $row->getEntityId();
        $id2 = "#popup-modal" . $row->getEntityId();
        $html = '';

        switch ($row->getCheckStatus()) {
            case Vproducts::APPROVED_STATUS:
                $html = '<a class="disapprove-product" for="' . $id2 . '" href="javascript:void(0);" title="' .
                    __("Click to Disapprove") . '">' . __("Disapprove") . '</a>';
                $reasonhtml =  $this->getReasonHtml(
                    $this->getUrl('csmarketplace/vproducts/changeStatus/status/0/id/' . $row->getId()),
                    $id
                );
                $html = $html . $reasonhtml;
                break;

            case Vproducts::PENDING_STATUS:
                $html = '<a href="' . $this->getUrl('csmarketplace/vproducts/changeStatus/status/1/id/' . $row->getId()) .
                    '"  title="' . __("Click to Approve") .
                    '" onclick="return confirm(\'Are you sure, You want to approve?\')">' . __("Approve") . '</a>
                 |<a class="disapprove-product" for="' . $id2 . '" href="javascript:void(0);" title="' .
                    __("Click to Disapprove") . '">' . __("Disapprove") . '</a>';
                $reasonhtml = $this->getReasonHtml(
                    $this->getUrl('csmarketplace/vproducts/changeStatus/status/0/id/' . $row->getId()),
                    $id
                );
                $html = $html . $reasonhtml;
                break;

            case Vproducts::NOT_APPROVED_STATUS:
                $html = '<a href="' . $this->getUrl('csmarketplace/vproducts/changeStatus/status/1/id/' . $row->getId()) .
                    '"  title="' . __("Click to Approve") .
                    '" onclick="return confirm(\'Are you sure, You want to approve?\')">' . __("Approve") . '</a>';
                break;
        }

        return $html;
    }

    /**
     * @param $url
     * @param $id
     * @return string
     */
    function getReasonHtml($url, $id)
    {
        $formkey = $this->formKey->getFormKey();
        $divContent = "<form style='display:none' 
                             id='" . $id . "' 
                             method='post' 
                             action='" . $url . "'>
                             <div>
                                <textarea cols='50' rows='8' name='reason' ></textarea>
                             </div>
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
                        $('.disapprove-product').on('click',function(){ 
                            var openId = $(this).attr('for');
                            $(openId).modal('openModal');
                        });

                    }
                );
            </script>";

        return $divContent . $popupcontent;
    }
}
