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

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Details;


use Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname;
use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Model\ResourceModel\Vsettings\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Locale\Currency;
use Magento\Framework\Registry;

/**
 * Class Form
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Details
 */
class Form extends Generic
{

    /**
     * @var Acl
     */
    protected $_acl;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Currency
     */
    protected $_localeCurrency;

    /**
     * @var Vendorname
     */
    protected $vendorname;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Currency $localeCurrency
     * @param Acl $acl
     * @param Vendorname $vendorname
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Currency $localeCurrency,
        Acl $acl,
        Vendorname $vendorname,
        CollectionFactory $vsettingfactory,
        array $data = []
    )
    {
        $this->_acl = $acl;
        $this->_coreRegistry = $registry;
        $this->_localeCurrency = $localeCurrency;
        $this->vendorname = $vendorname;
        $this->_vsettingsFactory = $vsettingfactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        list($model, $fieldsets) = $this->loadFields();
        $form = $this->_formFactory->create();
        foreach ($fieldsets as $key => $data) {
            $fieldset = $form->addFieldset($key, ['legend' => $data['legend']]);
            foreach ($data['fields'] as $id => $info) {
                if ($info['type'] == 'link') {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'href' => $info['href'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',
                    ]);
                } else {
                    $fieldset->addField($id, $info['type'], [
                        'name' => $id,
                        'label' => $info['label'],
                        'title' => $info['label'],
                        'value' => isset($info['value']) ? $info['value'] : $model->getData($id),
                        'text' => isset($info['text']) ? $info['text'] : $model->getData($id),
                        'after_element_html' => isset($info['after_element_html']) ? $info['after_element_html'] : '',
                    ]);
                }
            }
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Currency_Exception
     */
    protected function loadFields()
    {
        $model = $this->_coreRegistry->registry('csmarketplace_current_transaction');
        $renderName = $this->vendorname;
        $renderOrderDescBlock = $this->getLayout()->createBlock(
            'Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc'
        );

        $vendorSettingsConfig = $this->_vsettingsFactory->create()->addFieldToFilter('vendor_id', $model->getData('vendor_id'));
        $data = [];
        foreach ($vendorSettingsConfig as $vendorSettingData) {
            if ($model->getData('payment_code') == 'banktransfer') {
                if ($vendorSettingData['key'] == 'payment/vbanktransfer/bank_name')
                    $data['bank'] = $vendorSettingData['value'];
                if ($vendorSettingData['key'] == 'payment/vbanktransfer/bank_branch_number')
                    $data['branch'] = $vendorSettingData['value'];
                if ($vendorSettingData['key'] == 'payment/vbanktransfer/bank_swift_code')
                    $data['ifsc'] = $vendorSettingData['value'];
                if ($vendorSettingData['key'] == 'payment/vbanktransfer/bank_account_number')
                    $data['account'] = $vendorSettingData['value'];
                if ($vendorSettingData['key'] == 'payment/vbanktransfer/bank_account_name')
                    $data['holder'] = $vendorSettingData['value'];
            }
            if ($model->getData('payment_code') == 'cheque') {
                if ($vendorSettingData['key'] == 'payment/vcheque/cheque_payee_name')
                    $data['payee'] = $vendorSettingData['value'];
            }
        }
        $other = false;
        if ($model->getData('payment_code') == 'banktransfer') {
            $detail = 'Holder : ' . $data['holder'] . '<br>' . 'Bank : ' . $data['bank'] . '<br>' . 'Branch : ' .
                $data['branch'] . '<br>' . 'IFSC : ' . $data['ifsc'] . '<br>' . 'Account No : ' . $data['account'];
        } elseif ($model->getData('payment_code') == 'cheque') {
            $detail = $data['payee'];
        } else {
            $other = true;
            $detail = $model->getData('payment_code_other');
        }

        if ($model->getCurrency() != $model->getBaseCurrency()) {
            $fieldsets = [
                'beneficiary_details' => [
                    'fields' => [
                        'vendor_id' => [
                            'label' => __('Vendor Name'),
                            'text' => $renderName->render($model),
                            'type' => 'note'
                        ],
                        'payment_code' => [
                            'label' => __('Payment Method'),
                            'type' => 'label',
                            'value' => $model->getData('payment_code')
                        ],
                        'payment_detail' => [
                            'label' => $other ? __('Payment Details') : __('Beneficiary Details'),
                            'type' => 'note',
                            'text' => $detail
                        ],
                    ],
                    'legend' => $other ? __('Payment Details') : __('Beneficiary Details')
                ],

                'order_details' => [
                    'fields' => [
                        'amount_desc' => [
                            'label' => __('Order Details'),
                            'text' => $renderOrderDescBlock->render($model),
                            'type' => 'note',
                        ],
                    ],
                    'legend' => __('Amount Description')
                ],

                'payment_details' => [
                    'fields' => [
                        'transaction_id' => [
                            'type' => 'label',
                            'label' => __('Transaction ID#'),
                            'value' => $model->getData('transaction_id')
                        ],
                        'created_at' => [
                            'type' => 'label',
                            'label' => __('Transaction Date'),
                            'value' =>  $this->formatDate(
                                $this->_localeDate->date(new \DateTime($model->getData('created_at'))),
                                \IntlDateFormatter::MEDIUM,
                                true
                            ),
                        ],
                        'payment_method' => [
                            'label' => __('Transaction Mode'),
                            'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                            'type' => 'label',
                        ],
                        'base_amount' => [
                            'label' => __('Amount'),
                            'value' => $this->_localeCurrency->getCurrency(
                                $model->getBaseCurrency()
                            )->toCurrency($model->getData('base_amount')),
                            'type' => 'label',
                        ],
                        'amount' => [
                            'label' => '',
                            'value' => '[' . $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('amount')) . ']',
                            'type' => 'label',
                        ],
                        'base_fee' => [
                            'label' => __('Adjustment Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_fee')),
                            'type' => 'label',
                        ],
                        'fee' => [
                            'label' => '',
                            'value' => '[' . $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('fee')) . ']',
                            'type' => 'label',
                        ],
                        'base_net_amount' => [
                            'label' => __('Paid Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_net_amount')),
                            'type' => 'label',
                        ],
                        'net_amount' => [
                            'label' => '',
                            'value' => '[' . $this->_localeCurrency->getCurrency($model->getCurrency())
                                    ->toCurrency($model->getData('net_amount')) . ']',
                            'type' => 'label',
                        ],
                        'notes' => [
                            'label' => __('Notes'),
                            'value' => $model->getData('notes'),
                            'type' => 'label',
                        ],
                    ],
                    'legend' => __('Transaction Details')
                ]
            ];
        }elseif(empty($model->getData('notes'))) {
            $fieldsets = [
                'order_details' => [
                    'fields' => [
                        'amount_desc' => [
                            'label' => __('Order Details'),
                            'text' => $renderOrderDescBlock->render($model),
                            'type' => 'note',
                        ],
                    ],
                    'legend' => __('Amount Description')
                ],
                'beneficiary_details' => [
                    'fields' => [
                        'payment_code' => ['label' => __('Payment Method'), 'type' => 'label',
                            'value' => $model->getData('payment_code')],
                        'payment_detail' => ['label' => $other ? __('Payment Details') : __('Beneficiary Details'),
                            'type' => 'note',
                            'text' => $detail],
                        'vendor_id' => ['label' => __('Vendor Name'), 'text' => $renderName->render($model),
                            'type' => 'note'],
                    ],
                    'legend' => $other ? __('Payment Details') : __('Beneficiary Details')
                ],

                'payment_details' => [
                    'fields' => [
                        'payment_method' => ['label' => __('Transaction Mode'),
                            'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                            'type' => 'label',],
                        'created_at' => [
                            'label' => __('Transaction Date'),
                            'value' => $this->formatDate(
                                $this->_localeDate->date(new \DateTime($model->getData('created_at'))),
                                \IntlDateFormatter::MEDIUM,
                                true
                            ),
                            'type' => 'label',
                        ],
                        'transaction_id' => ['label' => __('Transaction ID#'), 'type' => 'label',
                            'value' => $model->getData('transaction_id')],
                        'base_amount' => [
                            'label' => __('Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_amount')),
                            'type' => 'label',
                        ],
                        'base_net_amount' => [
                            'label' => __('Paid Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_net_amount')),
                            'type' => 'label',
                        ],
                        'base_fee' => [
                            'label' => __('Adjustment Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_fee')),
                            'type' => 'label',
                        ],
                    ],
                    'legend' => __('Transaction Details')
                ],
            ];
        } else {
            $fieldsets = [
                'order_details' => [
                    'fields' => [
                        'amount_desc' => [
                            'text' => $renderOrderDescBlock->render($model),
                            'type' => 'note',
                            'label' => __('Order Details'),
                        ],
                    ],
                    'legend' => __('Amount Description')
                ],

                'beneficiary_details' => [
                    'fields' => [
                        'payment_code' => ['label' => __('Payment Method'), 'type' => 'label',
                            'value' => $model->getData('payment_code')],
                        'vendor_id' => ['label' => __('Vendor Name'), 'text' => $renderName->render($model),
                            'type' => 'note'],
                        'payment_detail' => ['label' => $other ? __('Payment Details') : __('Beneficiary Details'),
                            'type' => 'note',
                            'text' => $detail],
                    ],
                    'legend' => $other ? __('Payment Details') : __('Beneficiary Details')
                ],

                'payment_details' => [
                    'fields' => [
                        'payment_method' => [
                            'label' => __('Transaction Mode'),
                            'value' => $this->_acl->getDefaultPaymentTypeLabel($model->getData('payment_method')),
                            'type' => 'label',
                        ],
                        'transaction_id' => ['label' => __('Transaction ID#'), 'type' => 'label',
                            'value' => $model->getData('transaction_id')],
                        'created_at' => [
                            'label' => __('Transaction Date'),
                            'value' => $this->formatDate(
                                $this->_localeDate->date(new \DateTime($model->getData('created_at'))),
                                \IntlDateFormatter::MEDIUM,
                                true
                            ),
                            'type' => 'label',
                        ],
                        'base_amount' => [
                            'label' => __('Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_amount')),
                            'type' => 'label',
                        ],
                        'base_fee' => [
                            'label' => __('Adjustment Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_fee')),
                            'type' => 'label',
                        ],
                        'base_net_amount' => [
                            'label' => __('Paid Amount'),
                            'value' => $this->_localeCurrency->getCurrency($model->getBaseCurrency())
                                ->toCurrency($model->getData('base_net_amount')),
                            'type' => 'label',
                        ],
                        'notes' => [
                            'label' => __('Notes'),
                            'value' => $model->getData('notes'),
                            'type' => 'label',
                        ],
                    ],
                    'legend' => __('Transaction Details')
                ],
            ];
        }

        return [$model, $fieldsets];
    }
}
