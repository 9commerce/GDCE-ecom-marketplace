<?php

namespace Ced\CsGst\Model\Order\Pdf;

use Magento\Framework\Serialize\Serializer\Json;
/**
 * Sales Order Creditmemo Pdf default items renderer
 */
class Admincreditmemo extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\Creditmemo
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        Json $serializer,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsGst\Helper\Data $gstHelper,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->gstHelper = $gstHelper;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $serializer,
            $string,
            $resource,
            $resourceCollection,
            $data
        );
    }



    /**
     * Draw item line
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function draw()
    {   $this->_objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $items = $this->getChildren($item);
        $prevOptionId = '';
        $drawItems = [];
        $leftBound = 35;
        $rightBound = 565;

        foreach ($items as $childItem) {
            $x = $leftBound;
            $line = [];

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 15];
            }

            // draw selection attributes
            if ($childItem->getOrderItem()->getParentItem()) {
                if ($prevOptionId != $attributes['option_id']) {
                    $line[0] = [
                        'font' => 'italic',
                        'text' => $this->string->split($attributes['option_label'], 38, true, true),
                        'feed' => $x,
                    ];

                    $drawItems[$optionId] = ['lines' => [$line], 'height' => 15];

                    $line = [];
                    $prevOptionId = $attributes['option_id'];
                }
            }

            // draw product titles
            if ($childItem->getOrderItem()->getParentItem()) {
                $feed = $x + 5;
                $name = $this->getValueHtml($childItem);
            } else {
                $feed = $x;
                $name = $childItem->getName();
            }

            $line[] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];

            $x += 100;

            // draw SKUs
            if (!$childItem->getOrderItem()->getParentItem()) {
                $text = [];
                foreach ($this->string->split($item->getSku(), 17) as $part) {
                    $text[] = $part;
                }
                $line[] = ['text' => $text, 'feed' => $x];
            }

            $x += 100;

            // draw prices
            if ($this->canShowPriceInfo($childItem)) {
                // draw Total(ex)
            	$text = $childItem->getQty() * 1;
            	$line[] = [
                	'text' => $childItem->getQty() * 1,
                	'feed' => $x+10,
                	'font' => 'bold',
                	'align' => 'center',
                	'width' => 30,
            	];
            	
            	$text = $order->formatPriceTxt($childItem->getRowTotal());
                $line[] = ['text' => $text, 'feed' => $x+50, 'font' => 'bold', 'align' => 'right', 'width' => 50];
                $x += 50;
                // draw QTY
               
                $x += 30;


                $igst = 'N/A';
                $sgst = 'N/A';
                $cgst = 'N/A';
                $vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($childItem->getProductId());
                if($vendorId){
                    $warehouse =  $this->gstHelper->getWarehouseForVendor($vendorId);
                }else{
                    $warehouse = $this->gstHelper->getWarehouseForAdmin();
                }

                if(!$order->getShippingAddress()){
                    $regionSelector = $order->getBillingAddress();
                }else{
                    $regionSelector = $order->getShippingAddress();
                }

                $shipping_source = $regionSelector->getRegionId();
                $qty = (int)$childItem->getQty();
                $amt = $childItem->getTaxAmount();
    
                $hsn = $this->gstHelper->getHsnByProductId($childItem->getProductId());
             
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
    
                $line[] = [
                    'text' => $igst,
                    'feed' => 380,
                    'font' => 'bold',
                    'align' => 'right',
                ];
             
                $line[] = [
                    'text' => $sgst,
                    'feed' => 440,
                    'font' => 'bold',
                    'align' => 'right',
                ];

                $line[] = [
                    'text' => $cgst,
                    'feed' => 500,
                    'font' => 'bold',
                    'align' => 'right',
                ];

                // draw Total(inc)
                $text = $order->formatPriceTxt(
                        $childItem->getRowTotal() + $childItem->getTaxAmount() - $childItem->getDiscountAmount()
                );
                    
                $line[] = ['text' => $text, 'feed' => $rightBound, 'font' => 'bold', 'align' => 'right'];
            }
            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = [];
                    $lines[][] = [
                        'text' => $this->string->split(
                            $this->filterManager->stripTags($option['label']),
                            40,
                            true,
                            true
                        ),
                        'font' => 'italic',
                        'feed' => $leftBound,
                    ];

                    if ($option['value']) {
                        $text = [];
                        $printValue = isset(
                            $option['print_value']
                        ) ? $option['print_value'] : $this->filterManager->stripTags(
                            $option['value']
                        );
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            foreach ($this->string->split($value, 30, true, true) as $subValue) {
                                $text[] = $subValue;
                            }
                        }

                        $lines[][] = ['text' => $text, 'feed' => $leftBound + 5];
                    }

                    $drawItems[] = ['lines' => $lines, 'height' => 15];
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, ['table_header' => true]);
        $this->setPage($page);
    }
}