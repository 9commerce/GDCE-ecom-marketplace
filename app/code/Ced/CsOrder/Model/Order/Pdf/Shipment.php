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

class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * Shipment constructor.
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Ced\CsOrder\Model\ShipmentFactory $shipmentFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
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
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->appEmulation = $appEmulation;
        $this->shipmentFactory = $shipmentFactory;
        $this->vOrderFactory = $vordersFactory;
    }

    /**
     * Return PDF document
     * @param array $shipments
     * @return \Zend_Pdf
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $vendorId = $this->customerSession->getVendorId();

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            $shipmentId = $shipment->getEntityId();
            $shipmentCollection = $this->shipmentFactory->create()->load($shipmentId);

            if ($shipment->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $pageShip = $this->newPage();
            $order = $shipment->getOrder();
            $orderId = $order->getIncrementId();
            $vOrders = $this->vOrderFactory->create()->load($orderId,'order_id');
            $vOrders->getShippingDescription();
            $shippingMethods = $vOrders->getShippingDescription();

            /* Add image */
            $this->insertLogo($pageShip, $shipment->getStore());
            /* Add address */
            $this->insertAddress($pageShip, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $pageShip,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                ),
                $shippingMethods
            );
            /* Add document text and number */
            $this->insertDocumentNumber($pageShip, __('Packing Slip # ') . $shipment->getIncrementId());
            /* Add table */
            $this->_drawHeader($pageShip);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($item->getOrderItem()->getVendorId() != $vendorId) {
                    continue;
                } else {
                    /* Draw item */
                    $this->_drawItem($item, $pageShip, $order);
                    $pageShip = end($pdf->pages);
                }
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            $this->appEmulation->stopEnvironmentEmulation();
        }
        return $pdf;
    }

    /**
     * Insert order to pdf page
     * @param \Zend_Pdf_Page $pageShip
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @throws \Zend_Pdf_Exception
     */
    protected function insertOrder(&$pageShip, $obj, $putOrderId = true,$shippingMethods = null)
    {
        $vorderShip = $this->registry->registry('current_vorder');

        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pageShip->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $pageShip->drawRectangle(25, $top, 570, $top - 55);
        $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($pageShip, 10);

        if ($putOrderId) {
            $pageShip->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }
        $pageShip->drawText(
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
        $pageShip->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $pageShip->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $pageShip->setLineWidth(0.5);
        $pageShip->drawRectangle(25, $top, 275, $top - 25);
        $pageShip->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billing_address = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $payment_Info = $this->_paymentData->getInfoBlock($order->getPayment())
            ->setArea('adminhtml')->setIsSecureMode(true)->toPdf();

        $payment_Info = htmlspecialchars_decode($payment_Info, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $payment_Info);
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
            $shippingMethod = $shippingMethods;
        }

        $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($pageShip, 12);
        $pageShip->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $pageShip->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $pageShip->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billing_address);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $pageShip->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($pageShip, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billing_address as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $pageShip->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
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
                        $pageShip->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $pageShip->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $pageShip->setLineWidth(0.5);
            $pageShip->drawRectangle(25, $this->y, 275, $this->y - 25);
            $pageShip->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($pageShip, 12);
            $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $pageShip->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $pageShip->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($pageShip, 10);
            $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                /* Printing "Payment Method" lines */
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $pageShip->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            /* Replacement of Shipments-Payments rectangle block */
            $yPayments = min($addressesEndY, $yPayments);
            $pageShip->drawLine(25, $top - 25, 25, $yPayments);
            $pageShip->drawLine(570, $top - 25, 570, $yPayments);
            $pageShip->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;
            $yShipments = $this->y;
            if (isset($shippingMethod) && \is_string($shippingMethod)) {
                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $pageShip->drawText(strip_tags(trim($_value ?: '')), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __('Total Shipping Charges') . " " .
                $order->formatPriceTxt($order->getShippingAmount()) . ")";
            $pageShip->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $pageShip->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $pageShip->setLineWidth(0.5);
                $pageShip->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $pageShip->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($pageShip, 9);
                $pageShip->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $pageShip->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $pageShip->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($pageShip, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $pageShip->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $pageShip->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            /* Replacement of Shipments-Payments rectangle block */
            $pageShip->drawLine(25, $methodStartY, 25, $currentY);
            /* Left */
            $pageShip->drawLine(25, $currentY, 570, $currentY);
            /* Bottom */
            $pageShip->drawLine(570, $currentY, 570, $methodStartY);
            /* Right */

            $this->y = $currentY;
            $this->y -= 15;
        }
    }
}
