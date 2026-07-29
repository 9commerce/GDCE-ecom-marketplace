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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\Vpayment;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Registry;


/**
 * Class Edit
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments
 */
class Edit extends Container
{
    /**
     * @var null
     */
    protected $_availableMethods = null;

    /**
     * @var Header
     */
    protected $_header;

    /**
     * @var Acl
     */
    protected $_acl;

    /**
     * @var Vendor
     */
    protected $_vendor;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Vpayment
     */
    protected $vPaymentModel;

    /**
     * @param Vpayment $vPaymentModel
     * @param Context $context
     * @param Registry $registry
     * @param Header $header
     * @param Acl $acl
     * @param Vendor $vendor
     * @param array $data
     */
    public function __construct(
        Vpayment $vPaymentModel,
        Context $context,
        Registry $registry,
        Header $header,
        Acl $acl,
        Vendor $vendor,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_header = $header;
        $this->_acl = $acl;
        $this->_vendor = $vendor;
        $this->vPaymentModel = $vPaymentModel;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getHeaderText()
    {
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) &&
        in_array(trim($params['type']), array_keys($this->vPaymentModel->getStates())) ?
            trim($params['type']) : Vpayment::TRANSACTION_TYPE_CREDIT;

        return ($type == Vpayment::TRANSACTION_TYPE_DEBIT) ? __("Debit Amount") : __("Credit Amount");
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $amount = $this->getRequest()->getPost('total', 0);
        $this->_objectId = 'paymentid';
        $this->_controller = 'adminhtml_vpayments';
        $this->_blockGroup = 'Ced_CsMarketplace';
        $url = $this->_header->getHttpReferer() && preg_match('/\/index\//i', $this->_header->getHttpReferer()) ?
            $this->_header->getHttpReferer() : $this->getUrl('*/*/index');
        parent::_construct();

        $this->updateButton('back', 'onclick', "setLocation('" . $url . "')");
        if ($amount) {
            $this->removeButton('save');

             $this->addButton('save', array(
                'label' => __('Pay') . ' ' . $this->_acl->getDefaultPaymentTypeLabel(),
                'onclick' => 'save()',
                'class' => count($this->availableMethods()) == 0 ? 'save disabled' : 'save primary',
                count($this->availableMethods()) == 0 ? 'disabled' : '' => count($this->availableMethods()) == 0 ?
                    true : '',
            ), -100);


             $this->_formScripts[] = " 
                                    function save(){
                                        var editForm = jQuery('#edit_form');
                                        if($(editForm).valid()){
                                            editForm.submit();
                                            jQuery('#save').addClass('disabled');
                                        }
                                     }";

        } else {


            $this->removeButton('save');


            $this->addButton('saveandcontinue', array(
                'label' => __('Continue'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => count($this->availableMethods()) == 0 ? 'save disabled' : 'save primary',
                count($this->availableMethods()) == 0 ? 'disabled' : '' => count($this->availableMethods()) == 0 ?
                    true : '',
            ), -100);

            $this->_formScripts[] = " 
                                    function saveAndContinueEdit(){
                                       

                                        var editForm = jQuery('#edit_form');
                            editForm.attr('action',editForm.attr('action')+'back/edit/'+csaction);

                                            editForm.submit();
                                     }";
        }
    }

    /**
     * @return array|null
     */
    public function availableMethods()
    {
        if ($this->_availableMethods == null) {
            $vendorId = $this->getRequest()->getParam('vendor_id', 0);
            $this->_availableMethods = $this->_vendor->getPaymentMethodsArray($vendorId);
        }
        return $this->_availableMethods;
    }
}
