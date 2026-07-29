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

namespace Ced\CsMarketplace\Block\Vorders\View\Totals;

/**
 * Class Tax
 * @package Ced\CsMarketplace\Block\Vorders\View\Totals
 */
class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{

    /**
     * @var \Magento\Sales\Model\Order\Tax
     */
    protected $tax;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $calculation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Tax constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Sales\Model\Order\Tax $tax
     * @param \Magento\Tax\Model\Calculation $calculation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Sales\Model\Order\Tax $tax,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->tax = $tax;
        $this->calculation = $calculation;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * @return mixed
     */
    public function getCurrentVorder()
    {
        return $this->registry->registry('current_vorder');
    }

    /**
     * Get full information about taxes applied to order
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFullTaxInfo()
    {
        /** @var \Magento\Sales\Model\Order $source */
        $source = $this->getOrder();
        $info = array();
        if ($source instanceof \Magento\Sales\Model\Order) {

            $rates = $this->tax->getCollection()->loadByOrder($source)->toArray();
            $info = $this->calculation->reproduceProcess($rates['items']);

            /**
             * Set right tax amount from invoice
             * (In $info tax invalid when invoice is partial)
             */
            $blockInvoice = $this->getLayout()->getBlock('tax');
            /** @var Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $blockInvoice->getSource();
            $items = $invoice->getItemsCollection();
            $i = 0;
            /** @var Magento\Sales\Model\Order\Invoice\Item $item */
            foreach ($items as $item) {
                $info[$i]['hidden'] = $item->getHiddenTaxAmount();
                $info[$i]['amount'] = $item->getTaxAmount();
                $info[$i]['base_amount'] = $item->getBaseTaxAmount();
                $info[$i]['base_real_amount'] = $item->getBaseTaxAmount();
                $i++;
            }
        }

        return $info;
    }

    /**
     * Display tax amount
     *
     * @param $amount
     * @param $baseAmount
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->displayPrices(
            $this->getSource(), $baseAmount, $amount, false, '<br />'
        );
    }

    /**
     * Get "double" prices html (block with base and place currency)
     *
     * @param $dataObject
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return  string
     */
    public function displayPrices($dataObject, $basePrice, $price, $strong = false, $separator = '<br/>')
    {
        if ($dataObject instanceof \Magento\Sales\Model\Order) {
            $order = $dataObject;
        } else {
            $order = $dataObject->getOrder();
        }
        $res = '';
        if ($order && $order->isCurrencyDifferent()) {
            $res .= $order->formatBasePrice($basePrice);
            $res .= $separator;
            $res .= '[' . $order->formatPrice($price) . ']';
        } elseif ($order) {
            $res = $order->formatPrice($price);
        } else {
            $res = $this->storeManager->getStore(null)->formatPrice($price);
        }
        return $res;
    }

    /**
     * Get store object for process configuration settings
     *
     * @return Magento\Store\Model\StoreManagerInterface
     */
    public function getStore()
    {
        return $this->storeManager->getStore(null);
    }
}
