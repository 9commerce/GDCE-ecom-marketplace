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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Model\Order\Pdf;

class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customersession;
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * Invoice constructor.
     *
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Customer\Model\Session $customersession
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Registry $registry,
        \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory,
        array $data = []
    ) {
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
        $this->appEmulation = $appEmulation;
        $this->registry = $registry;
        $this->customersession = $customersession;
        $this->invoiceFactory = $invoiceFactory;
    }

    /**
     * Return PDF document
     * @param array $invoices
     * @return \Zend_Pdf
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($invoices = [])
    {
        $vendorId = $this->customersession->getVendorId();
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            $invoiceId = $invoice->getEntityId();
            $invoiceData = $this->invoiceFactory->create()->load($invoiceId);

            if ($invoice->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $pageInv = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($pageInv, $invoice->getStore());
            /* Add address */
            $this->insertAddress($pageInv, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $pageInv,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                ),
                $invoiceData
            );
            /* Add document text and number */
            $this->insertDocumentNumber($pageInv, __('Invoice # ') . $invoice->getIncrementId());
            /* Add table */
            $this->_drawHeader($pageInv);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($item->getOrderItem()->getVendorId() != $vendorId) {
                    continue;
                } else {
                    /* Draw item */
                    $this->_drawItem($item, $pageInv, $order);
                    $pageInv = end($pdf->pages);
                }
            }
            /* Add totals */
            $this->insertTotals($pageInv, $invoice);
            if ($invoice->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Insert order to pdf page
     * @param \Zend_Pdf_Page $pageInv
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @throws \Zend_Pdf_Exception
     */
    protected function insertOrder(&$pageInv, $obj, $putOrderId = true,$invoiceData = null)
    {
        $vorderInv = $this->registry->registry('current_vorder');
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pageInv->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pageInv->drawRectangle(25, $top, 570, $top - 55);
        $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($pageInv, 10);

        if ($putOrderId) {
            $pageInv->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }
        $pageInv->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        $pageInv->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $pageInv->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $pageInv->setLineWidth(0.5);
        $pageInv->drawRectangle(25, $top, 275, $top - 25);
        $pageInv->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billing_Address = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $payment_info = $this->_paymentData->getInfoBlock($order->getPayment())
            ->setArea('adminhtml')->setIsSecureMode(true)->toPdf();

        $paymentInfo = htmlspecialchars_decode($payment_info, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
//            $shippingMethod = $order->getShippingDescription();
            $shippingMethod = $invoiceData->getShippingDescription();
        }

        $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($pageInv, 12);
        $pageInv->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $pageInv->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $pageInv->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billing_Address);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $pageInv->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($pageInv, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billing_Address as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $pageInv->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $pageInv->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $pageInv->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $pageInv->setLineWidth(0.5);
            $pageInv->drawRectangle(25, $this->y, 275, $this->y - 25);
            $pageInv->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($pageInv, 12);
            $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $pageInv->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $pageInv->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($pageInv, 10);
            $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $pageInv->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $pageInv->drawLine(25, $top - 25, 25, $yPayments);
            $pageInv->drawLine(570, $top - 25, 570, $yPayments);
            $pageInv->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;
            $yShipments = $this->y;
            if (isset($shippingMethod) && \is_string($shippingMethod)) {
                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $pageInv->drawText(strip_tags(trim($_value ?: '')), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }

                $yShipments = $this->y;
                $totalShippingChargesText = "(" . __('Total Shipping Charges') . " " .
                    $order->formatPriceTxt($order->getShippingAmount()) . ")";
                $pageInv->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            }
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $pageInv->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $pageInv->setLineWidth(0.5);
                $pageInv->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $pageInv->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($pageInv, 9);
                $pageInv->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $pageInv->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $pageInv->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($pageInv, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $pageInv->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $pageInv->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $pageInv->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $pageInv->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $pageInv->drawLine(570, $currentY, 570, $methodStartY);
            //right
            $this->y = $currentY;
            $this->y -= 15;
        }
    }
}
