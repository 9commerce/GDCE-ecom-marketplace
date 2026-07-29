<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced
 * @package     Ced_CsGst
 * @author 		CedCommerce Core Team <coreteam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CsGst\Model\Total;
class GstFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    protected $_helper;

    protected $gstHelper;

    protected $vproductFactory;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
      \Ced\CsGst\Helper\Data $gstHelper,
      \Ced\CsMarketplace\Model\VproductsFactory $vproductFactory
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->gstHelper = $gstHelper;
        $this->vproductFactory = $vproductFactory;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {

        parent::collect($quote, $shippingAssignment, $total);

        if ($this->gstHelper->moduleEnable()) {

                if(!$quote->getShippingAddress()->getRegionId()){

                    $regionSelector = $quote->getBillingAddress();
                }else{
                     $regionSelector = $quote->getShippingAddress();
                }
    			$shipping_source = $regionSelector->getRegionId();

    		    if ($shipping_source != '') {
    		    	foreach ($quote->getAllItems() as $quoteItem){
    			        $product = $quoteItem->getProduct();
    			        $vendorId = $this->vproductFactory->create()->getVendorIdByProduct($product->getId());
    			        if($vendorId){
    			        	$taxclassId = $this->gstHelper->getTaxClassIdForVendor($product,$shipping_source,$vendorId);
    			        	$product->setTaxClassId($taxclassId);
    			        }
    			        else{

    				        $taxclassId = $this->gstHelper->getTaxClassIdForAdmin($product,$shipping_source);
    				        $product->setTaxClassId($taxclassId);

    			        }
    			    }

    		}
        }
        return $this;
    }

}
