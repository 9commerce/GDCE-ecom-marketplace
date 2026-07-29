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

namespace Ced\CsTransaction\Block\Adminhtml\Vorders\Grid\Renderer;

use Ced\CsMarketplace\Model\Vorders;

class Paynow extends \Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Paynow
{
    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_vtItemsFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vtItemsCollectionFactory;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * Paynow constructor.
     * @param \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vtItemsCollectionFactory,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        $this->_vtItemsFactory = $vtItemsFactory;
        $this->_vtItemsCollectionFactory = $vtItemsCollectionFactory;
        $this->csorderHelper = $csorderHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getPayNowButtonHtml($url = '')
    {
        return '<input class="button sacalable save" style="
        background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;
        border-color: #ed6502 #a04300 #a04300 #ed6502; border-style: solid; border-width: 1px;
        color: #fff; cursor: pointer; font: bold 12px arial,helvetica,sans-serif;
        padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;"
        type="button" onclick="setLocation(\'' . $url . '\')" value="PayNow">';
    }

    /**
     * @param string $url
     * @param string $label
     * @return string
     */
    protected function getRefundButtonHtml($url = '', $label = '')
    {
        return '<input class="button sacalable save"
        style="background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;
        border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;    border-width: 1px;    color: #fff;
        cursor: pointer;    font: bold 12px arial,helvetica,sans-serif;
        padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;"
        type="button" onclick="setLocation(\'' . $url . '\')" value="RefundNow">';
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (!$this->csorderHelper->isActive()) {
            return parent::render($row);
        }

        $vorderItem = $this->_vtItemsFactory->create();
        $html = '';
        $collection = $this->_vtItemsCollectionFactory->create()
            ->addFieldToFilter('parent_id', $row->getId())->addFieldToFilter('vendor_id', $row->getVendorId());
        $can_pay = false;
        if (!empty($collection->getData())) {
            foreach ($collection as $_collection) {
                if ($_collection->getQtyOrdered() == $_collection->getQtyReadyToPay() + $_collection
                        ->getQtyRefunded()) {
                    $can_pay = true;
                    break;
                }
            }
        }
        if ($row->getPaymentState() == Vorders::STATE_PAID) {
            $html .= __('Paid');
        } elseif ($row->getPaymentState() == Vorders::STATE_CANCELED) {
            $html .= __('Cancelled');
        } elseif ($can_pay) {
            $html .= __('Pending');
            $itemIds = $vorderItem->canPay($row->getVendorId(), $row->getOrderId());
            $url = $this->getUrl(
                'csmarketplace/vpayments/new/',
                [
                    'vendor_id' => $row->getVendorId(),
                    'order_ids' => $itemIds,
                    'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT
                ]
            );
            $html .= "&nbsp;" . $this->getPayNowButtonHtml($url);
        } elseif ($row->getPaymentState() == \Ced\CsOrder\Model\Vorders::STATE_PARTIALLY_PAID) {
            $html .= __('Partially Paid');
        } elseif ($row->getPaymentState() == Vorders::STATE_REFUND) {
            $html .= __('Refund');
        } else {
            $html .= __('Pending');
        }
        return $html;
    }
}
