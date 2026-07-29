<?php

namespace Ced\CsGst\Model\Order\Pdf;

class Creditmemo extends \Magento\Sales\Model\Order\Pdf\Creditmemo
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Draw table header for product items
     *
     * @param  \Zend_Pdf_Page $page
     * @return void
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

    /**
     * Return PDF document
     *
     * @param  array $creditmemos
     * @return \Zend_Pdf
     */
    public function getPdf($creditmemos = [])
    {   
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                $this->_localeResolver->emulate($creditmemo->getStoreId());
                $this->_storeManager->setCurrentStore($creditmemo->getStoreId());
            }
            $page = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Credit Memo # ') . $creditmemo->getIncrementId());
            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        }
        $this->_afterGetPdf();
        if ($creditmemo->getStoreId()) {
            $this->_localeResolver->revert();
        }
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        $page = parent::newPage($settings);
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }


    //Add a Code Regarding a Font issue for INR CURRENCY 
    
     protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->getFontsDir() . ('devaju-sans/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        /*$font = \Zend_Pdf_Font::fontWithPath(
            $this->getFontsDir() . ('devaju-sans/DejaVuSans-Bold.ttf')
        );*/
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->getFontsDir() . ('devaju-sans/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        /*$font = \Zend_Pdf_Font::fontWithPath(
            $this->getFontsDir() . ('devaju-sans/DejaVuSansCondensed-Oblique.ttf')
        );*/
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->getFontsDir() . ('devaju-sans/DejaVuSans.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
    protected function getFontsDir()
    {
        $path = 'lib/internal/';
        return $this->_rootDirectory->getAbsolutePath($path);
    }
}
