<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsGst\Model\Order\Pdf\Items\Invoice;

/**
 * Sales Order Invoice Pdf default items renderer
 */
class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * DefaultInvoice constructor.
     * @param \Ced\CsGst\Helper\Data $gstHelper
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsGst\Helper\Data $gstHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->string = $string;
        $this->gstHelper = $gstHelper;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [['text' => $this->string->split($item->getName(), 15, true, true), 'feed' => 35]];

        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($this->getSku($item), 17),
            'feed' => 230,
            'align' => 'right',
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 275, 'align' => 'right'];

        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 329;
        $feedSubtotal = $feedPrice + 230;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedPrice, 'align' => 'right'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'right'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => $feedPrice,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            $i++;
        }

        
        
        
        $igst = 'N/A';
        $sgst = 'N/A';
        $cgst = 'N/A';
    	$vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($item->getProductId());
    if($vendorId){
    	$warehouse =  $this->gstHelper->getWarehouseForVendor($vendorId);
    }else{
    	$warehouse =  $this->gstHelper->getWarehouseForAdmin();
    }
   
        
    if(!$order->getShippingAddress()){
    	 
    	$regionSelector = $order->getBillingAddress();
    }else{
    	$regionSelector = $order->getShippingAddress();
    }
    $shipping_source = $regionSelector->getRegionId();
    
        $qty = (int)$item->getQty();
        $amt = $item->getTaxAmount();
      
        $hsn = $this->gstHelper->getHsnByProductId($item->getProductId());
       
        if ($amt > 0) {
        	if ($warehouse == $shipping_source) {
        		$igst = 'N/A';
        		$sgst = $order->formatPriceTxt($amt/2);
        		$cgst = $order->formatPriceTxt($amt/2);
        	}else{
        		$sgst = 'N/A';
        		$cgst = 'N/A';
        		$igst = $order->formatPriceTxt($amt);
        	}
        }else{
        	$igst = 'N/A';
        	$sgst = 'N/A';
        	$cgst = 'N/A';
        }
        
        	$lines[0][] = [
        	'text' => $igst,
        	'feed' => 360,
        	'font' => 'bold',
        	'align' => 'right',
        	];
        	
        	$lines[0][] = [
        	'text' => $sgst,
        	'feed' => 420,
        	'font' => 'bold',
        	'align' => 'right',
        	];
        	
        	$lines[0][] = [
        	'text' => $cgst,
        	'feed' => 480,
        	'font' => 'bold',
        	'align' => 'right',
        	];
        	
        //end tax draw for gst

        // custom options
        $options = $this->getItemOptions();
        
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                if ($option['value']) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }
        
       

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
