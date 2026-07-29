<?php

namespace Ced\CsGst\Model\Order\Pdf\Items\Creditmemo;

/**
 * Sales Order Creditmemo Pdf default items renderer
 */
class DefaultCreditmemo extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsGst\Helper\Data $gstHelper,
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

    public function draw()
    {
    	$order = $this->getOrder();
    	$item = $this->getItem();
    	$pdf = $this->getPdf();
    	$page = $this->getPage();
    	$lines = [];
    
    	// draw Product name
    	$lines[0] = [['text' => $this->string->split($item->getName(), 35, true, true), 'feed' => 35]];
    
    	// draw SKU
    	$lines[0][] = [
            'text' => $this->string->split($this->getSku($item), 17),
            'feed' => 200,
            'align' => 'right',
    	];
    
    	// draw QTY
    	$lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 280, 'align' => 'right'];
    
    	// draw item Prices
    	 $subtotal = $item->getRowTotal() +
            $item->getTaxAmount() +
            $item->getDiscountTaxCompensationAmount() -
            $item->getDiscountAmount();
        $lines[0][] = [
            'text' => $order->formatPriceTxt($subtotal),
            'feed' => 550,
            'font' => 'bold',
            'align' => 'right',
        ];
    
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getRowTotal()),
            'feed' => 320,
            'font' => 'bold',
            'align' => 'right',
        ];
    
    
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
    
    	$hsn =  $this->gstHelper->getHsnByProductId($item->getProductId());
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
        	'feed' => 380,
        	'font' => 'bold',
        	'align' => 'right',
    	];

    	$lines[0][] = [
            'text' => $sgst,
            'feed' => 440,
            'font' => 'bold',
            'align' => 'right',
    	];

    	$lines[0][] = [
            'text' => $cgst,
            'feed' => 500,
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
