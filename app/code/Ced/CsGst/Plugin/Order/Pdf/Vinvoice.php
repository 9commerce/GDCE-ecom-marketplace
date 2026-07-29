<?php

namespace Ced\CsGst\Plugin\Order\Pdf;

use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * Sales Order Invoice PDF model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Vinvoice extends \Ced\CsOrder\Model\Order\Pdf\Invoice
{


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
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
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
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory,
        array $data = []

    ){
        parent::__construct(
            $paymentData,
            $string,
            $storeManager,
            $appEmulation,
            $customersession,
            $addressRenderer,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $scopeConfig,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $registry,
            $invoiceFactory
        );
        $this->customersession = $customersession;
        $this->vproducts = $vproducts;
        $this->registry = $registry;
        $this->vendor = $vendor;
    }


     /**
     * Return PDF document
     *
     * @param  array|Collection $invoices
     * @return \Zend_Pdf
     */

	protected function _drawHeader(\Zend_Pdf_Page $page)
	{

		/* Add table head */
		$this->_setFontRegular($page, 10);
		$page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
		$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
		$page->setLineWidth(0.5);
		$page->drawRectangle(25, $this->y, 570, $this->y - 15);
		$this->y -= 10;
		$page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

		//columns headers
		$lines[0][] = ['text' => __('Products'), 'feed' => 35];

		$lines[0][] = ['text' => __('SKU'), 'feed' => 200, 'align' => 'right'];

		$lines[0][] = ['text' => __('Qty'), 'feed' => 280, 'align' => 'right'];

		$lines[0][] = ['text' => __('Price'), 'feed' => 320, 'align' => 'right'];

		$lines[0][] = ['text' => __('Tax(IGST)'), 'feed' => 380, 'align' => 'right'];

		$lines[0][] = ['text' => __('Tax(SGST)'), 'feed' => 440, 'align' => 'right'];

		$lines[0][] = ['text' => __('Tax(CGST)'), 'feed' => 500, 'align' => 'right'];

		$lines[0][] = ['text' => __('Subtotal'), 'feed' => 550, 'align' => 'right'];

		$lineBlock = ['lines' => $lines, 'height' => 5];

		$this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
		$page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
		$this->y -= 20;
	}

    public function aroundGetPdf(\Ced\CsVendorAttribute\Model\Rewrite\Order\Pdf\Invoice $subject , callable $proceed , $invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $vendorId = $this->customersession->getVendorId();
        $vProducts = $this->vproducts;


        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId().'   GSTIN: '.$this->vendor->load($vendorId)->getVendorGstin());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                if ($vProducts->getVendorIdByProduct($item->getProductId())!= $vendorId) {
                    continue;
                }
                else {
                    /* Draw item */
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);
                }

            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

}
