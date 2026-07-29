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

namespace Ced\CsMarketplace\Block\Adminhtml\Vproducts\View\Button;


use Ced\CsMarketplace\Model\Vproducts;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Action
 * @package Ced\CsMarketplace\Block\Adminhtml\Vproducts\View\Button
 */
class Action extends Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * Action constructor.
     * @param FormKey $formKey
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FormKey $formKey,
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as $key => $item)
        {
            $id = "popup-modal".$item['entity_id'];
            $id2 = "#popup-modal".$item['entity_id'];
            switch($item['check_status']) {
                case Vproducts::APPROVED_STATUS:
                    $html='<a class="disapprove-product" for="' . $id2 .
                        '" href="javascript:void(0);" title="' .
                        __("Click to Disapprove").'">' . __("Disapprove") .'</a>';

                    $reasonhtml = $this->getReasonHtml(
                        $this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/0/id/' . $item['entity_id']
                        ),
                        $id
                    );
                    $html = $html.$reasonhtml;
                    $dataSource['data']['items'][$key]['actions'] = $html;
                    break;

                case Vproducts::PENDING_STATUS:
                    $html='<a href="'.$this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/1/id/' . $item['entity_id']).'"  
                    title="'.__("Click to Approve").'" onclick="if(confirm(\'Are you sure, You want to approve?\')); 
                    setLocation(\''.$this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/1/id/' .
                            $item['entity_id']).'\')"">'.__("Approve").'</a>
                 |<a class="disapprove-product" for="'.$id2.'" href="javascript:void(0);" 
                 title="'.__("Click to Disapprove").'">'.__("Disapprove").'</a>';

                    $reasonhtml = $this->getReasonHtml(
                        $this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/0/id/' . $item['entity_id']
                        ),
                        $id
                    );
                    $html = $html.$reasonhtml;
                    $dataSource['data']['items'][$key]['actions'] = $html;
                    break;

                case Vproducts::NOT_APPROVED_STATUS:
                    $html = '<a type = "submit" href="' . $this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/1/id/' . $item['entity_id']) . '"  
                    title="' . __("Click to Approve") . '" 
                onclick="if(confirm(\'Are you sure, You want to approve?\')); 
                setLocation(\''.$this->urlBuilder->getUrl(
                            'csmarketplace/vproducts/changeStatus/status/1/id/' .
                            $item['entity_id']).'\')">' . __("Approve") . '</a>';

                    $dataSource['data']['items'][$key]['actions'] = $html;
                    break;
            }
        }
        return $dataSource;
    }

    /**
     * @param $url
     * @param $id
     * @return string
     */
    function getReasonHtml($url, $id){
        $formkey = $this->formKey->getFormKey();
        $divContent ="<form style='display:none' id='".$id."' method='post' action='".$url."'>
                <div><textarea cols='50' rows='8' name='reason'></textarea></div>
             <input type='hidden' name='form_key' value='".$formkey."'/>
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
                                     jQuery('#'+'".$id."').submit();
            		                 this.closeModal();
                                }
                            }]
                        };

                        var popup = modal(options, $('#'+'".$id."'));
                        $('.disapprove-product').on('click',function(){ 
                            var openId = $(this).attr('for');
                            $(openId).modal('openModal');
                        });

                    }
                );
            </script>";

        return $divContent.$popupcontent;
    }

}