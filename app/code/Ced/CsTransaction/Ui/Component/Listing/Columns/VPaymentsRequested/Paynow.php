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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Ui\Component\Listing\Columns\VPaymentsRequested;

use Ced\CsMarketplace\Model\Vpayment;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Paynow extends Column
{
    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_itemsResource;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $_csorderHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */

    protected $_vordersFactory;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $_vordersResource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Paynow constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Ced\CsTransaction\Model\ItemsFactory $itemsFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items $itemsResource
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Ced\CsTransaction\Model\ItemsFactory $itemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $itemsResource,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_itemsFactory = $itemsFactory;
        $this->_itemsResource = $itemsResource;
        $this->_csorderHelper = $csorderHelper;
        $this->_vordersFactory = $vordersFactory;
        $this->_vordersResource = $vordersResource;
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
       
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $html = "";
                if ($item['vendor_id'] != '' && $item['status'] != 2) {
                    if ($this->_csorderHelper->isActive()) {
                        $vorderItem = $this->_itemsFactory->create();
                        $orderId = $item['order_id'];
                        $itemIds = '';
                        if ($orderId) {
                            $order = $this->_vordersFactory->create();
                            $this->_vordersResource->load($order, $orderId, 'order_id');
                            $itemIds = $vorderItem->canPay($item['vendor_id'], $order->getOrderId());
                        }
                        if (strlen($itemIds) > 0) {
                            $url = $this->_urlBuilder->getUrl(
                                'csmarketplace/vpayments/new/',
                                [
                                    'vendor_id' => $item['vendor_id'],
                                    'order_ids' => $item['vorder_item_id'],
                                    'type' => Vpayment::TRANSACTION_TYPE_CREDIT
                                ]
                            );
                            $html .= "&nbsp;" . $this->getPayNowButtonHtml($url);
                        }
                    } else {
                        $url = $this->_urlBuilder->getUrl(
                            'csmarketplace/vpayments/new/',
                            [
                                'vendor_id' => $item['vendor_id'],
                                'order_ids' => $item['order_ids'],
                                'type' => Vpayment::TRANSACTION_TYPE_CREDIT
                            ]
                        );
                        $html .= "&nbsp;" . $this->getPayNowButtonHtml($url);
                    }
                }

                $item['action'] = $html;
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
        return '<input class="button sacalable save" style="cursor: pointer;
        background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;
        border-color: #ed6502 #a04300 #a04300 #ed6502;
        border-style: solid;
        border-width: 1px;    color: #fff;    cursor: pointer;
        font: bold 12px arial,helvetica,sans-serif;
        padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" type="button"
        onclick="setLocation(\'' . $url . '\')" value="PayNow">';
    }
}
