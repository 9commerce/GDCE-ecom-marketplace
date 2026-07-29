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

namespace Ced\CsTransaction\Ui\Component\Listing\Columns\RequestedTransaction;

use Ced\CsTransaction\Model\Items;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_itemsResource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Ced\CsTransaction\Model\ItemsFactory $itemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $itemsResource,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->_itemsFactory = $itemsFactory;
        $this->_itemsResource = $itemsResource;
        $this->_sessionFactory = $sessionFactory;
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
                $html = '';
                $model = $this->_itemsFactory->create();
                $this->_itemsResource->load($model, $item['id']);
                if ($model->getIsRequested() == 1 && $model->getItemPaymentState() == Items::STATE_READY_TO_PAY) {
                    $html .= __('Requested');
                } elseif ($model->getItemPaymentState() == Items::STATE_PAID) {
                    $html .= __('Paid');
                } elseif ($model->getQtyOrdered() == $model->getQtyRefunded()) {
                    $html .= __('Cancelled');
                } elseif ($model->getQtyOrdered() == $model->getQtyReadyToPay() + $model->getQtyRefunded()) {
                    $url = $this->_urlBuilder->getUrl(
                        'cstransaction/vpayments/requestpost',
                        ['payment_request' => $item['id']]
                    );
                    $html .= $this->getRequestButtonHtml($url);
                } else {
                    $html .= __('Not Allowed');
                }
                $item['action']= $html;
            }
        }
        return $dataSource;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getRequestButtonHtml($url = '')
    {
        return '<input class="button scalable save" style="cursor: pointer;
        background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;
        border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;
        border-width: 1px;    color: #fff;    cursor: pointer;
        font: bold 12px arial,helvetica,sans-serif;
        padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;"
        type="button"  onclick="window.location.assign(\'' . $url . '\')" value="Request">';
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->_sessionFactory->create()->getVendorId();
    }
}
