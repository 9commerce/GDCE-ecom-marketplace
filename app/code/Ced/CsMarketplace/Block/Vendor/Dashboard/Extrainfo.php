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

namespace Ced\CsMarketplace\Block\Vendor\Dashboard;


use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Locale\Currency;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class Extrainfo
 * @package Ced\CsMarketplace\Block\Vendor\Dashboard
 */
class Extrainfo extends AbstractBlock
{

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @var InvoiceFactory
     */
    public $invoiceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * Extrainfo constructor.
     * @param CollectionFactory $statusCollectionFactory
     * @param InvoiceFactory $invoiceFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Currency $currency
     */
    public function __construct(
        CollectionFactory $statusCollectionFactory,
        InvoiceFactory $invoiceFactory,
        PriceCurrencyInterface $priceCurrency,
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Currency $currency
    ) {
        $this->currency = $currency;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->storeManager = $context->getStoreManager();
        $this->priceCurrency = $priceCurrency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        if ($this->getVendorId()) {
            $ordersCollection = $this->getVendor()
                ->getAssociatedOrders()->setOrder('id', 'DESC');
            $main_table = 'main_table';
            $order_total = 'base_order_total';
            $shop_commission_fee = 'shop_commission_base_fee';
            $ordersCollection->getSelect()
                ->columns([
                    'net_vendor_earn' => new \Zend_Db_Expr(
                        "({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})"
                    )
                ])
                ->order('created_at DESC')->limit(5);
            $this->setVorders($ordersCollection);
        }
    }

    /**
     * Return order view link
     *
     * @param string $order
     * @return String
     */
    public function getViewUrl($order)
    { 
        return $this->getUrl(
            '*/vorders/view',
            [
                'order_id' => $order->getRealOrderId(),
                'vorder_id' => $order->getId()
            ]
        );
    }

    /**
     * @return Currency
     */
    public function getVcurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getOrderStatusArray()
    {
        $statuses = $this->statusCollectionFactory->create()->toOptionArray();
        $status_arr = [];
        foreach ($statuses as $status) {
            $status_arr[$status['value']] = $status['label'];
        }
        return $status_arr;
    }
}
