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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Component\Listing\Columns;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Ced\CsMarketplace\Model\System\Config\VendorPaymentStatus;

/**
 * Class PaymentStateLink
 * @package Ced\CsMarketplace\Ui\Component\Listing\Columns
 */
class PaymentStateLink extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * PaymentStateLink constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource){
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $buttonText = $buttonhtml = '';
                if ($item['payment_state']){
                    if ($item['payment_state'] == VendorPaymentStatus::STATE_REFUND){
                        $buttonText = __('Paid');
                    }
                    if ($item['payment_state'] == VendorPaymentStatus::STATE_OPEN){
                        $buttonText = __('Pending');
                    }
                    if ($item['payment_state'] == VendorPaymentStatus::STATE_PAID){
                        $buttonText = __('Paid');
                    }

                    if ($item['payment_state'] == VendorPaymentStatus::STATE_CANCELED){
                        $buttonText = __('Canceled');
                    }
                    if ($item['payment_state'] == VendorPaymentStatus::STATE_REFUNDED){
                        $buttonText = __('Refunded');
                    }

                    if ($item['order_payment_state'] == \Magento\Sales\Model\Order\Invoice::STATE_PAID
                        &&
                        $item['payment_state'] == VendorPaymentStatus::STATE_OPEN)
                    {
      
                        $url = $this->urlBuilder->getUrl('csmarketplace/vpayments/new/',
                            ['vendor_id' => $item['vendor_id'], 'order_ids' =>$item['id'],
                                'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT]);
                        $buttonText .="&nbsp;".$this->getPayNowButtonHtml($url);
                        $item[$this->getData('name')] = $buttonText;
                    }else{
                        $item[$this->getData('name')] = $buttonText;
                    }


                }
            }
        }
        return $dataSource;
    }  


    /**
     * @param string $url
     * @return string
     */
    protected function getPayNowButtonHtml($url = '')
    {
        return '<input class="button sacalable save" style="cursor: pointer; background: #ffac47 
        url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    
        border-style: solid; border-width: 1px; color: #fff; cursor: pointer; font: bold 12px arial,
        helvetica,sans-serif; padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" 
        type="button" onclick="setLocation(\''.$url.'\')" value="PayNow">';
    }

    /**
     * @param string $url
     * @param string $label
     * @return string
     */
    protected function getRefundButtonHtml($url = '', $label = '')
    {
        return '<input class="button sacalable save" style="cursor: pointer; background: #ffac47 
        url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    
        border-style: solid;    border-width: 1px; color: #fff; cursor: pointer;    
        font: bold 12px arial,helvetica,sans-serif; padding: 1px 7px 2px;text-align: center !important; 
        white-space: nowrap;" type="button" onclick="setLocation(\''.$url.'\')" value="RefundNow">';
    }  
}
