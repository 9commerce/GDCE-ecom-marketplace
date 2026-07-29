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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vpayments;

use Magento\Backend\App\Action\Context;

/**
 * Class Save
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vpayments
 */
class Save extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $vpaymentFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $mailHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Directory\Helper\Data $helperData
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Ced\CsMarketplace\Helper\Mail $mailHelper
     */
    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Directory\Helper\Data $helperData,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper
    ) {
        $this->vpaymentFactory = $vpaymentFactory;
        $this->helperData = $helperData;
        $this->currencyFactory = $currencyFactory->create();
        $this->mailHelper = $mailHelper;
        parent::__construct($context);

    }

    /**
     * Customer edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $params = $this->getRequest()->getParams();
            $model = $this->vpaymentFactory->create();

            $type = isset($params['type']) &&
            in_array($params['type'], array_keys($model->getStates())) ? $params['type'] :
                \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;

            $amount_desc = isset($data['amount_desc']) ? $data['amount_desc'] : json_encode([]);
            $total_amount = json_decode($amount_desc);
            if (is_object($total_amount)) {
                $total_amount = (array)$total_amount;
            }

            $baseCurrencyCode = $this->helperData->getBaseCurrencyCode();
            $allowedCurrencies = $this->currencyFactory->getConfigAllowCurrencies();
            $rates = $this->currencyFactory->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            $data['base_to_global_rate'] =
                isset($data['currency']) && isset($rates[$data['currency']]) ? $rates[$data['currency']] : 1;

            $base_amount = 0;
            if (count($total_amount) > 0) {
                foreach ($total_amount as $key => $value) {
                    $base_amount += $value;
                }
            }

            if ($base_amount != $data['base_amount']) {
                $this->messageManager->addErrorMessage(
                    __('Amount entered should be equal to the sum of all selected order(s)')
                );
                return $this->_redirect('*/*/edit',
                        ['vendor_id' => $this->getRequest()->getParam('vendor_id'), 'type' => $type]
                    );

            }

            $data['transaction_type'] = $type;
            $data['payment_method'] = isset($data['payment_method']) ? $data['payment_method'] : 0;
            /*Will use it when vendor will pay in different currenncy  */
			$data['notes'] = isset($data['notes']) ? $data['notes'] : '';

            list($currentBalance, $currentBaseBalance) = $model->getCurrentBalance($data['vendor_id']);

            $base_net_amount = floatval($data['base_amount']) - floatval($data['base_fee']);
            if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
                /* Case of Deduct credit */
                if ($currentBaseBalance > 0) {
                    $newBaseBalance = $currentBaseBalance - $base_net_amount;
                } else {
                    $newBaseBalance = $base_net_amount;
                }
                $base_net_amount = -$base_net_amount;
                if (-$base_net_amount <= 0.00) {
                    $this->messageManager->addErrorMessage("Refund Net Amount can't be less than zero");
                    return $this->_redirect('*/*/edit',
                        ['vendor_id' => $this->getRequest()->getParam('vendor_id'), 'type' => $type]);
                }
            } else {
                // Case of Add credit 
                $newBaseBalance = $currentBaseBalance + $base_net_amount;
                if ($base_net_amount <= 0.00) {
                    $this->messageManager->addErrorMessage("Net Amount can't be less than zero");
                    return $this->_redirect('*/*/edit',
                        ['vendor_id' => $this->getRequest()->getParam('vendor_id'), 'type' => $type]);

                }
            }
              $data['base_currency']= $baseCurrencyCode;
              $data['base_net_amount'] = $base_net_amount;
              $data['base_balance'] = $newBaseBalance;
            
              $data['amount'] = $base_amount*$data['base_to_global_rate'];
              $data['balance'] = $this->helperData->currencyConvert($newBaseBalance,
                  $baseCurrencyCode, $data['currency']);
              $data['fee'] = $this->helperData->currencyConvert(floatval($data['base_fee']),
                  $baseCurrencyCode, $data['currency']);
              $data['net_amount'] = $this->helperData->currencyConvert($base_net_amount,
                  $baseCurrencyCode, $data['currency']);
            
              $data['tax'] = 0.00;
              $data['payment_detail'] = isset($data['payment_code_other'])?$data['payment_code_other']:'N/A';

              $ti = $model->load($data['transaction_id']);
              if ($ti->getTransactionId()){
                  $this->messageManager->addErrorMessage(__('Transaction id already exist'));
                  return $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
              }

              $model->addData($data->toArray());    

            $openStatus = $model->getOpenStatus();
            $model->setStatus($openStatus);

            try {
                $model->saveOrders($data);
                $model->save();
                $this->mailHelper->sendSellerTransactionEmail($model);
                $this->messageManager->addSuccessMessage(__('Payment is  successfully saved'));
                $this->_session->setFormData(false);
                return $this->_redirect('*/*/');

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setFormData($data);
                return $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find vendor to save'));
        return $this->_redirect('*/*/');
    }
}