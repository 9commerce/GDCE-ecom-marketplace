<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMarketplace\Model\Order\Pdf;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Sales Order Invoice PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transaction extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var mixed
     */
    protected $filter;

    /**
     * @var mixed
     */
    protected $collectionFactory;

    /**
     * @var mixed
     */
    protected $fileFactory;

    /**
     * @var mixed
     */
    protected $vendorFactory;

    /**
     * @var mixed
     */
    protected $priceCurrency;

    /**
     * @var mixed
     */
    protected $inlineTranslation;

    /**
     * @var mixed
     */
    protected $vordersFactory;

    /**
     * @var mixed
     */
    protected $_acl;

    /**
     * @var mixed
     */
    protected $y;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context            $context,
        \Magento\Framework\View\Result\PageFactory       $resultPageFactory,
        Session                                          $customerSession,
        UrlFactory                                       $urlFactory,
        \Magento\Framework\Registry                      $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data                   $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl                    $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory           $vendor,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        Filter $filter,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Helper\Acl $acl
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->vendorFactory = $vendorFactory;
        $this->registry = $registry;
        $this->priceCurrency = $priceCurrency;
        $this->inlineTranslation = $inlineTranslation;
        $this->vordersFactory = $vordersFactory;
        $this->session = $customerSession;
        $this->_acl = $acl;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }


    public function getPdf($vendorcollection = [])
    {
        $vendor = $this->_getSession()->getVendor();
        $vendorId = $this->_getSession()->getVendorId();

        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0];
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $vCollection = $vendorcollection->addFieldToFilter('vendor_id', $vendorId);

        $pageCount = 0;
        foreach ($vCollection as $item) {
            if($pageCount) {
                $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
                $page = $pdf->pages[$pageCount];
                $style->setFont($font,12);
                $page->setStyle($style);
            }
            $style->setFont($font,15);
            $page->setStyle($style);
            $width = $page->getWidth();
            $hight = $page->getHeight();
            $x = 80;
            $pageTopalign = 850;
            $this->y = 850 - 100;
            $style->setFont($font,16);
            $page->setStyle($style);
            $page->drawRectangle(30, $this->y - 20, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $style->setFont($font,20);
            $page->setStyle($style);
            $page->drawText(__("Transaction Details"), $x - 20, $this->y+50, 'UTF-8');
            $style->setFont($font,14);
            $page->setStyle($style);
            $page->drawText(__("Beneficiary Details"), $x - 10, $this->y+25, 'UTF-8');
            $page->drawText(__("Name : %1", $vendor['name']), $x + 5, $this->y+6, 'UTF-8');
            $page->drawText(__("Email : %1",$vendor['email']), $x + 5, $this->y-9, 'UTF-8');
            $page->drawText(__("Payment Method : "), $x + 40, $this->y-40, 'UTF-8');
            $page->drawText(__("Transaction Mode : "), $x + 40, $this->y-60, 'UTF-8');
            $page->drawText(__("Transaction Id : "), $x + 40, $this->y-80, 'UTF-8');
            $page->drawText(__("Amount : "), $x + 40, $this->y-100, 'UTF-8');
            $page->drawText(__("Total Shipping Amount : "), $x + 40, $this->y-120, 'UTF-8');
            $page->drawText(__("Base Amount : "), $x + 40, $this->y-140, 'UTF-8');
            $page->drawText(__("Adjustment Amount : "), $x + 40, $this->y-160, 'UTF-8');
            $page->drawText(__("Base Adjustment Amount : "), $x + 40, $this->y-180, 'UTF-8');
            $page->drawText(__("Net Amount : "), $x + 40, $this->y-200, 'UTF-8');
            $page->drawText(__("Transaction Date : "), $x + 40, $this->y-220, 'UTF-8');
            $page->drawText(__("Base Net Amount : "), $x + 40, $this->y-240, 'UTF-8');

            $page->drawText(__($item->getPaymentCode()), $x + 200, $this->y-40, 'UTF-8');
            $page->drawText(__($this->_acl->getDefaultPaymentTypeLabel($item->getPaymentMethod())), $x + 200, $this->y-60, 'UTF-8');
            $page->drawText(__($item->getTransactionId()), $x + 200, $this->y-80, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getAmount(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-100, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getTotalShippingAmount(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-120, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getBaseAmount(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-140, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getFee(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-160, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getBaseFee(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-180, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getNetAmount(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-200, 'UTF-8');
            $page->drawText(__($item->getCreatedAt()), $x + 200, $this->y-220, 'UTF-8');
            $page->drawText(__($this->priceCurrency->format($item->getBaseNetAmount(), false, 2, null, $item->getCurrency())), $x + 200, $this->y-240, 'UTF-8');

            $style->setFont($font,16);
            $page->setStyle($style);
            $page->drawText(__("Order Details"), $x - 10, $this->y-270, 'UTF-8');

            $style->setFont($font,13);
            $page->setStyle($style);

            $amountDesc = json_decode($item->getAmountDesc(),true);
            if(!isset($amountDesc[0])) {

                $page->drawText("Order Id", $x - 5, $this->y-300, 'UTF-8');
                $page->drawText("Order Total", $x + 110, $this->y-300, 'UTF-8');
                $page->drawText("Commission Fee", $x + 250, $this->y-300, 'UTF-8');
                $page->drawText('Net Earned', $x + 390, $this->y-300, 'UTF-8');

                $style->setFont($font,11);
                $page->setStyle($style);

                $vModel = $this->vordersFactory->create();
                $y = $this->y - 320;
                foreach (json_decode($item->getAmountDesc(),true) as $amountData => $value) {
                    $orderId = $amountData;
                    $vOrderModel = $vModel->loadByField(['order_id', 'vendor_id'], [$orderId, $vendorId]);
                    $page->drawText($orderId, $x - 5, $y, 'UTF-8');
                    $page->drawLine($x + 80, $this->y - 279, $x + 80 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($vOrderModel->getOrderTotal(), false, 2, null, $item->getCurrency()), $x + 110, $y, 'UTF-8');
                    $page->drawLine($x + 200, $this->y - 279, $x + 200 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($vOrderModel->getShopCommissionFee(), false, 2, null, $item->getCurrency()), $x + 250, $y, 'UTF-8');
                    $page->drawLine($x + 370, $this->y - 279, $x + 370 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($item->getNetAmount(), false, 2, null, $item->getCurrency()), $x + 390, $y, 'UTF-8');

                    $y = $y-20;
                }
            } else {
                $page->drawText("OrderId", $x - 5, $this->y-300, 'UTF-8');
                $page->drawText("Grand Total", $x + 60, $this->y-300, 'UTF-8');
                $page->drawText("Commission Fee", $x + 150, $this->y-300, 'UTF-8');
                $page->drawText('Payment Mode', $x + 260, $this->y-300, 'UTF-8');
                $page->drawText('Total Payment', $x + 360, $this->y-300, 'UTF-8');

                $style->setFont($font,11);
                $page->setStyle($style);

                $y = $this->y - 320;
                foreach (json_decode($item->getAmountDesc(),true) as $amountData) {
                    $page->drawText($amountData['order_id'], $x - 5, $y, 'UTF-8');
                    $page->drawLine($x + 50, $this->y - 279, $x + 50 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($amountData['order_total'], false, 2, null, $item->getCurrency()), $x + 60, $y, 'UTF-8');
                    $page->drawLine($x + 140, $this->y - 279, $x + 140 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($amountData['commission_fee'], false, 2, null, $item->getCurrency()), $x + 150, $y, 'UTF-8');
                    $page->drawLine($x + 250, $this->y - 279, $x + 250 , $y - 20 );
                    $page->drawText($amountData['order_paymode'], $x + 260, $y, 'UTF-8');
                    $page->drawLine($x + 350, $this->y - 279, $x + 350 , $y - 20 );
                    $page->drawText($this->priceCurrency->format($amountData['vendor_payment'], false, 2, null, $item->getCurrency()), $x + 360, $y, 'UTF-8');

                    $y = $y-20;
                }
            }
            $page->drawRectangle(30, $this->y - 279, $page->getWidth()-30, $this->y + 40, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->drawRectangle(30, $this->y - 252, $page->getWidth()-30,  $y, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $pageCount++;
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * After getPdf processing
     *
     * @return void
     */
    protected function _afterGetPdf()
    {
        $this->inlineTranslation->resume();
    }

    /**
     *
     * Retrieve customer session model object
     *
     * @return Session
     */
    protected function _getSession()
    {
        return $this->session;
    }

}
