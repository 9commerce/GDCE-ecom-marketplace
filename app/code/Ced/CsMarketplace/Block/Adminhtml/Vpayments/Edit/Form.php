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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit;


use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Model\Vpayment;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Form
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit
 */
class Form extends Generic
{

    /**
     * @var Acl
     */
    protected $_acl;

    /**
     * @var Vpayment
     */
    protected $vPaymentModel;

    /**
     * Form constructor.
     * @param Vpayment $vPaymentModel
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Acl $acl
     * @param array $data
     */
    public function __construct(
        Vpayment $vPaymentModel,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Acl $acl,
        array $data = []
    ) {
        $this->_acl = $acl;
        $this->vPaymentModel = $vPaymentModel;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return mixed
     */
    protected function _prepareForm()
    {
        $back = $this->getRequest()->getParam('back', '');
        $amount = $this->getRequest()->getPost('total', 0);
        $params = $this->getRequest()->getParams();
        $type = isset($params['type']) &&
        in_array($params['type'], array_keys($this->vPaymentModel->getStates())) ? $params['type'] :
            Vpayment::TRANSACTION_TYPE_CREDIT;

        if ($back == 'edit' && $amount) {
            $form = $this->_formFactory->create(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save',
                            array('payment_method' => $this->_acl->getDefaultPaymentType(), 'type' => $type)),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
                ]
            );
        } else {
            $form = $this->_formFactory->create(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/*', [
                            'vendor_id' => $this->getRequest()->getParam('vendor_id'), 'type' => $type
                        ]),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                    ]
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}