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

namespace Ced\CsMarketplace\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\UrlRewrite\Model\UrlRewriteFactory;


/**
 * Class SetVendorOrder
 * @package Ced\CsMarketplace\Model
 */
class SetVendorOrder extends \Ced\CsMarketplace\Model\AbstractModel
{

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $mailHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var VordersFactory
     */
    protected $vOrdersFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $helper;

    /**
     * SetVendorOrder constructor.
     * @param \Ced\CsMarketplace\Helper\Mail $mailHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param VordersFactory $vOrdersFactory
     * @param \Ced\CsMarketplace\Helper\Data $dataHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Url $url
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Mail $mailHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Ced\CsMarketplace\Model\VordersFactory $vOrdersFactory,
        \Ced\CsMarketplace\Helper\Data $dataHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        UrlRewriteFactory $urlRewriteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Url $url,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->vOrdersFactory = $vOrdersFactory;
        $this->_eventManager = $eventManager;
        $this->helper = $dataHelper;
        $this->mailHelper = $mailHelper;
        parent::__construct(
            $urlRewriteFactory,
            $storeManager,
            $url,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param $order
     * @return $this
     * @throws LocalizedException
     */
    public function setVendorOrder($order)
    {
        try {
            /** @var \Ced\CsMarketplace\Model\Vorders $vOrder */
            $vOrder = $this->vOrdersFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $order->getIncrementId())->getFirstItem();

            if ($vOrder->getId()) {
                $vOrder->setRealOrderStatus($order->getStatus());
                $vOrder->save();
                return $this;
            }

            $baseToGlobalRate = $order->getBaseToGlobalRate() ? $order->getBaseToGlobalRate() : 1;
            $vendorsBaseOrder = [];
            $vendorQty = [];

            foreach ($order->getAllItems() as $key => $item) {
                $vendor_id = $item->getVendorId();
                if ($vendor_id) {
                    if ($item->getHasChildren() && $item->getProductType() != 'configurable') {
                        continue;
                    }

                    $price = $item->getBaseRowTotal()
                        + $item->getBaseTaxAmount()
                        + $item->getBaseHiddenTaxAmount()
                        + $item->getBaseWeeeTaxAppliedRowAmount()
                        + $item->getBaseDiscountTaxCompensationAmount()
                        - $item->getBaseDiscountAmount();
                    $order_total = isset($vendorsBaseOrder[$vendor_id]['order_total']) ?
                        ($vendorsBaseOrder[$vendor_id]['order_total'] + $price) :
                        $price;

                    $vendorsBaseOrder[$vendor_id]['order_total'] = $order_total;
                    $vendorsBaseOrder[$vendor_id]['item_commission'][$item->getQuoteItemId()] = $price;
                    $vendorsBaseOrder[$vendor_id]['order_items'][] = $item;
                    $vendorQty[$vendor_id] = isset($vendorQty[$vendor_id]) ?
                        $vendorQty[$vendor_id] + $item->getQtyOrdered() :
                        $item->getQtyOrdered();
                    $logData = $item->getData();
                    unset($logData['product']);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logException($e);
            throw new LocalizedException(__('Error Occurred While Placing The Order'));
        }

        foreach ($vendorsBaseOrder as $vendorId => $baseOrderTotal) {
            try {
                $qty = isset($vendorQty[$vendorId]) ? $vendorQty[$vendorId] : 0;
                $vorder = $this->vOrdersFactory->create();
                $vorder->setVendorId($vendorId);
                $vorder->setCurrentOrder($order);
                $vorder->setOrderId($order->getIncrementId());
                $vorder->setCurrency($order->getOrderCurrencyCode());
                $vorder->setOrderTotal($this->directoryHelper->currencyConvert(
                    $baseOrderTotal['order_total'],
                    $order->getBaseCurrencyCode(),
                    $order->getOrderCurrencyCode())
                );

                $vorder->setBaseCurrency($order->getBaseCurrencyCode());
                $vorder->setBaseOrderTotal($baseOrderTotal['order_total']);
                $vorder->setBaseToGlobalRate($baseToGlobalRate);
                $vorder->setProductQty($qty);
                $billingaddress = $order->getBillingAddress()->getData();

                if (isset ($billingaddress ['middlename'])) {
                    $billing_name = $billingaddress ['firstname']
                        . " " .
                        $billingaddress ['middlename'] .
                        " " .
                        $billingaddress ['lastname'];
                } else {
                    $billing_name = $billingaddress ['firstname'] .
                        " " .
                        $billingaddress ['lastname'];
                }

                $vorder->setBillingName($billing_name);
                $vorder->setBillingCountryCode($order->getBillingAddress()->getData('country_id'));
                if ($order->getShippingAddress()) {
                    $vorder->setShippingCountryCode($order->getShippingAddress()->getData('country_id'));
                }
                $vorder->setItemCommission($baseOrderTotal['item_commission']);

                $vorder->collectCommission();

                //set order real entity id and status
                $vorder->setRealOrderId($order->getId());
                $vorder->setRealOrderStatus($order->getStatus());

                $vorder->setWebsiteId($order->getStore()->getWebsiteId());
                $this->_eventManager->dispatch(
                    'ced_csmarketplace_vorder_shipping_save_before',
                    ['vorder' => $vorder]
                );

                $vorder->save();
                $notificationData = [
                    'vendor_id' => $vendorId,
                    'reference_id' => $vorder->getId(),
                    'title' => 'New Order ' . $vorder->getOrderId(),
                    'action' => $this->helper->getUrl('csmarketplace/vorders/view', ['vorder_id' => $vorder->getId(),'order_id'=>$order->getId()])
                ];

                $this->helper->setNotification($notificationData);
                $this->mailHelper->sendOrderEmail($order, Vorders::ORDER_NEW_STATUS, $vendorId, $vorder);
            } catch (\Exception $e) {
                $this->helper->logException($e);
                throw new LocalizedException(__('Error Occurred While Placing The Order'));
            }
        }
        return $this;
    }

    /**
     * @param $order
     * @return $this
     * @throws LocalizedException
     */
    public function creditMemoOrder($order)
    {
        try {
            if ($order->getState() == Order::STATE_CLOSED ||
                ((float)$order->getBaseTotalRefunded() &&
                    (float)$order->getBaseTotalRefunded() >= (float)$order->getBaseTotalPaid())
            ) {
                $vorders = $this->vOrdersFactory->create()->getCollection()
                    ->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);
                if (count($vorders) > 0) {
                    foreach ($vorders as $vorder) {
                        if ($vorder->canCancel()) {
                            $vorder->setOrderPaymentState(Invoice::STATE_CANCELED);
                            $vorder->setPaymentState(Vorders::STATE_CANCELED);
                            $vorder->save();
                        } elseif ($vorder->canMakeRefund()) {
                            $vorder->setPaymentState(Vorders::STATE_REFUND);
                            $vorder->save();
                        }
                    }
                }
            }
            return $this;
        } catch (\Exception $e) {
            $this->helper->logException($e);
            throw new LocalizedException(__('Error Occurred While Placing The Order'));
        }
    }
}

