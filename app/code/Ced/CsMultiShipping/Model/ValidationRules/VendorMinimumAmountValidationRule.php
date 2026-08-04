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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
declare(strict_types=1);

namespace Ced\CsMultiShipping\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;
use Magento\Quote\Model\ValidationRules\QuoteValidationRuleInterface;
/**
 * @inheritdoc
 */
class VendorMinimumAmountValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var mixed
     */
    protected $_vsettingsFactory;

    /**
     * @var mixed
     */
    protected $csmarketplaceHelper;

    /**
     * @var mixed
     */
    protected $vendor;

    /**
     * @var mixed
     */
    protected $priceHelper;

    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationMessage
     */
    private $amountValidationMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationMessage $amountValidationMessage
     * @param ValidationResultFactory $validationResultFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper,
     * @param string $generalMessage
     */
    public function __construct(
        ValidationMessage $amountValidationMessage,
        ValidationResultFactory $validationResultFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        string $generalMessage = ''
    ) {
        $this->amountValidationMessage = $amountValidationMessage;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
        $this->_vsettingsFactory = $vsettingsFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendor = $vendorFactory;
        $this->priceHelper = $priceHelper;

    }

    /**
     * @inheritdoc
     * @throws \Zend_Currency_Exception
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $vendor_id_tmp = $this->csmarketplaceHelper->getTableKey('vendor_id');
        $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
        $validationPass = true;
        if($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/csmultishipping/min_order',$this->csmarketplaceHelper->getStore()->getId())){
            try{
                $vendorQuoteAmount = $this->getVendorQuoteAmount($quote);
                if(!empty($vendorQuoteAmount)){
                    foreach($vendorQuoteAmount as $vendorId=> $amount){
                         $setting = $this->_vsettingsFactory->create()
                            ->loadByField([$key_tmp, $vendor_id_tmp], ['shipping/address/min_order', (int)$vendorId]);
                        $value = $setting->getValue()?$setting->getValue():0.00;
                        $vendorData = $this->vendor->create()->load($vendorId);
                        if($amount<$value){
                            $validationPass = false;
                            $validationErrors[] = $this->getMessage($value,$vendorData);
                        }
                    }
                }
            
            }catch(\Exception $e){
                $validationPass = false;
                $this->generalMessage = __($e->getMessage());
            }
            if (!$validationPass) {
                if (!empty($validationErrors)) {
                    $this->generalMessage = implode(" ",$validationErrors);
                }
                $validationErrors = [__($this->generalMessage)];
                $quote->setHasError(true)->setMessage($this->generalMessage);

            }

        }
        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     * @inheritdoc
     * @return array
     */
    protected function getVendorQuoteAmount($quote): array{
        $vendorsOrderAmount = [];
        foreach ($quote->getAllVisibleItems() as $items) {
            if($items->getVendorId()){
                if(isset($vendorsOrderAmount[$items->getVendorId()]))
                    $vendorsAmount = ($items->getBaseRowTotal()+$vendorsOrderAmount[$items->getVendorId()]);
                else
                    $vendorsAmount = $items->getBaseRowTotal();

                $vendorsOrderAmount[$items->getVendorId()] = $vendorsAmount; 
            }
        }
        return $vendorsOrderAmount;
    }

    /**
     * @inheritdoc
     * @return string
     */
    protected function getMessage($value,$vendor){
        $minimumAmount =  $this->priceHelper->currency($value, true, false);
        return __('Min order amount %1 for the "%2" vendor is not reached.',$minimumAmount,$vendor->getName());
    }
}
