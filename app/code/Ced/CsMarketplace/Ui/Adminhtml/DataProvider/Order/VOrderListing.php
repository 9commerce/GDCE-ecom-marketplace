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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Adminhtml\DataProvider\Order;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;


/**
 * Class VOrderListing
 * @package Ced\CsMarketplace\Ui\Adminhtml\DataProvider\Order
 */
class VOrderListing extends AbstractDataProvider
{

    const STATE_OPEN       = 1;
    const STATE_PAID       = 2;
    const STATE_CANCELED   = 3;
    const STATE_REFUND     = 4;
    const STATE_REFUNDED   = 5;

    /**
     * @var
     */
    protected $vorders;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders\Collection
     */
    protected $collection;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * VOrderListing constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collection
     * @param UrlInterface $urlBuilder
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collection,
        UrlInterface $urlBuilder,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $meta = [],
        array $data =[]
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->vendorFactory = $vendorFactory;
        $this->collection = $collection->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->toArray();
        foreach ($items['items'] as $key => $values)
        {
            $html = '';
            $vendor = $this->vendorFactory->create()->load($items['items'][$key]['vendor_id']);
            $url =  $this->urlBuilder->getUrl("csmarketplace/vendor/edit/",
                array('vendor_id' => $vendor->getVendorId()));
            $target = "target='_blank'";
            $html .= '<a title="Click to view Vendor Details" onClick="setLocation(\''.$url.'\')"
            href="'.$url.'" "'.$target.'">'.$vendor->getName().'</a>';
            $items['items'][$key]['vendor_name'] = $html;
            $netEarned = $values['order_total'] - $values['shop_commission_fee'];
            $items['items'][$key]['vendor_payment'] = $netEarned;

            if ($values['payment_state'] == self::STATE_OPEN)
                $items['items'][$key]['payment_state'] = __('Pending');
            if ($values['payment_state'] == self::STATE_PAID)
                $items['items'][$key]['payment_state'] = __('Paid');
            if ($values['payment_state'] == self::STATE_CANCELED)
                $items['items'][$key]['payment_state'] = __('Canceled');
            if ($values['payment_state'] == self::STATE_REFUNDED)
                $items['items'][$key]['payment_state'] = __('Refunded');

            if ($values['order_payment_state'] == \Magento\Sales\Model\Order\Invoice::STATE_PAID
                &&
                $values['payment_state'] == self::STATE_OPEN)
            {
                $html = "";
                $url = $this->urlBuilder->getUrl('csmarketplace/vpayments/new/',
                    ['vendor_id' => $values['vendor_id'], 'order_ids' =>$values['id'],
                        'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT]);
                $html .="&nbsp;".$this->getPayNowButtonHtml($url);
                $items['items'][$key]['payment_state'] = $html;
            }
            elseif ($values['order_payment_state'] == \Magento\Sales\Model\Order\Invoice::STATE_PAID
                &&
                $values['payment_state'] == self::STATE_REFUND)
            {
                $url =  $this->urlBuilder->getUrl('csmarketplace/vpayments/new/',
                    array('vendor_id' => $values['vendor_id'], 'order_ids'=>$values['id'],
                        'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT));
                $html = $this->getRefundButtonHtml($url);
                $items['items'][$key]['payment_state'] = $html;
            }
        }
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($items['items']),
        ];
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
