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

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Grid\Renderer;

class Orderdesc extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc
{
    /**
     * @var bool
     */
    protected $_frontend = false;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_currencyInterface;

    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_vtItemsFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_vtItemsResource;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $orderHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $_orderResource;

    /**
     * Orderdesc constructor.
     * @param \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource
     * @param \Ced\CsOrder\Helper\Data $orderHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource,
        \Ced\CsOrder\Helper\Data $orderHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        array $data = []
    ) {
        $this->_vtItemsFactory = $vtItemsFactory;
        $this->_vtItemsResource = $vtItemsResource;
        $this->orderHelper = $orderHelper;
        $this->_orderFactory = $orderFactory;
        $this->_orderResource = $orderResource;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $design, $localeCurrency, $vordersFactory, $orderFactory, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return false|string
     * @throws \Zend_Currency_Exception
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (!$this->orderHelper->isActive()) {
            return parent::render($row);
        }
        $amountDesc = $row->getItem_wise_amount_desc();
        $html = '';
        $amountDesc = $amountDesc ? json_decode($amountDesc, true) : '';
        if (is_array($amountDesc)) {
            foreach ($amountDesc as $incrementId => $amounts) {
                if (is_array($amounts)) {
                    foreach ($amounts as $item_id => $baseNetAmount) {
                        if (is_array($baseNetAmount)) {
                            return false;
                        }
                        $url = 'javascript:void(0);';
                        $target = "";

                        $vorder = $this->orderFactory->create();
                        $this->_orderResource->load($vorder, $incrementId);
                        $incrementId = $vorder->getIncrementId();

                        if ($this->_frontend && $vorder && $vorder->getId()) {
                            $url = $this->getUrl("csmarketplace/vorders/view/", [
                                    'increment_id' => $incrementId
                                ]);
                            $target = "target='_blank'";
                            $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . "<a href='" .
                                    $url . "' " . $target . " >" . $incrementId . "</a>" . '</label><br/>';
                        } else {
                            $item = $this->_vtItemsFactory->create();
                            $this->_vtItemsResource->load($item, $item_id);
                            $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . $incrementId .
                                    ' : ' . $item->getSku() . '</label><br/>';
                        }
                    }
                }
            }
        } else {
            $amountDesc = $row->getAmountDesc();
            if ($amountDesc != '') {
                $amountDesc = json_decode($amountDesc, true);
                if (is_array($amountDesc)) {
                    foreach ($amountDesc as $incrementId => $baseNetAmount) {
                        if (is_array($baseNetAmount)) {
                            return false;
                        }
                        $url = 'javascript:void(0);';
                        $target = "";
                        $vorder = $this->orderFactory->create();
                        $this->_orderResource->load($vorder, $incrementId);
                        if ($this->_frontend && $vorder && $vorder->getId()) {
                            $url = $this->getUrl("csmarketplace/vorders/view/", [
                                    'increment_id' => $incrementId
                                ]);
                            $target = "target='_blank'";
                            $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . "<a href='" .
                                    $url . "' " . $target . " >" . $incrementId . "</a>" . '</label><br/>';
                        } else {
                            $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' .
                                    $incrementId . '</label><br/>';
                        }
                    }
                }
            }
        }
        if ($vendorId = $this->getRequest()->getParam('id')) {
            $html .= $this->getDetails($row);
        }
        if ($vendorId = $this->getRequest()->getParam('payment_id')) {
            $html .= $this->getDetails($row);
        }
        return $html;
    }

    /**
     * @param $row
     * @return bool|string
     */
    public function getDetails($row)
    {
        $amountDesc = $row->getItem_wise_amount_desc();
        $orderArray = json_decode($amountDesc, true);

        $html = "";
        if ($orderArray) {
            $html .= '<div class="grid" id="order-items_grid">
						<table cellspacing="0" class="data order-tables" style="width:50%; float:right" border="1">
							<col width="100" />
							<col width="40" />
							<col width="100" />
							<col width="80" />
							<thead>
								<tr class="headings" style="background-color: rgb(81, 73, 67); color: white;">';
            $html .= '<th class="no-link"><center>' . __("Order Id") . '</center></th>
										<th class="no-link"><center>' . __("Order Total") . '</center></th>
										<th class="no-link"><center>' . __("Commission Fee") . '</center></th>
										<th class="no-link"><center>' . __("Net Earned") . '</center></th>
								</tr>
							</thead>
							<tbody>';
            $class = '';

            foreach ($orderArray as $key => $value) {
                $class = ($class == 'odd') ? 'even' : 'odd';
                $html .= '<tr class="' . $class . '">';
                foreach ($value as $key1 => $value1) {
                    $html .= '<td><center>' . $this->getVendorOrderId($key1) . '</center></td>
												<td><center>' . $this->_currencyInterface
                                                ->getCurrency($row->getCurrency())
                                                ->toCurrency($value1) . '</center></td>
												<td><center>' . $this->_currencyInterface
                                                ->getCurrency($row->getCurrency())
                                                ->toCurrency($this
                                                ->getVendorItemCommission($key1, $value1)) . '</center></td>
												<td><center>' . $this->_currencyInterface
                                                ->getCurrency($row->getCurrency())
                                                ->toCurrency($value1 - $this->getVendorItemCommission($key1, $value1) + $row
                                                ->getTotalShippingAmount() - $row->getBaseFee()) . '</center></td></tr>';
                }
            }

            $html .= '</tbody></table><div><div><div><div>';
            return $html;
        }
        return false;
    }

    /**
     * @param $orderid
     * @param $value1
     * @return mixed
     */
    public function getVendorItemCommission($orderid, $value1)
    {
        $vorderItem = $this->_vtItemsFactory->create();
        $this->_vtItemsResource->load($vorderItem, $orderid);
        return $vorderItem->getItemCommission();
    }

    /**
     * @param $orderid
     * @return int
     */
    public function getVendorOrderId($orderid)
    {
        $vorder = $this->_vtItemsFactory->create();
        $this->_vtItemsResource->load($vorder, $orderid);
        $order_increment_id = 0;
        foreach ($vorder as $key => $value) {
            $order_increment_id = $value ['order_increment_id'];
        }
        return $order_increment_id;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        $formattedPrice = $this->pricingHelper->currency($price, true, false);
        return $formattedPrice;
    }
}
