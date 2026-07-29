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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesModelServiceQuoteSubmitBeforeObserver
 * @package Ced\CsMarketplace\Observer
 */
class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @var array
     */
    private $quoteItems = [];

    /**
     * @var null
     */
    private $quote = null;

    /**
     * @var null
     */
    private $order = null;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    private $helper;

    /**
     * SalesModelServiceQuoteSubmitBeforeObserver constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Helper\Data $helperData,
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Helper\Data $helperData,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_vendorFactory = $vendorFactory;
        $this->_state = $state;
        $this->helper = $helperData;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_state->getAreaCode() !== 'adminhtml') {
            $this->quote = $observer->getQuote();
            $this->order = $observer->getOrder();
            foreach ($this->order->getItems() as $orderItem) {
                if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId(), $this->quote)) {
                    $additionalOptions = [];
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        $additionalOptions = $additionalOptionsQuote->getValue();

                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptionstmp = $this->_serializer->unserialize($additionalOptionsQuote->getValue());
                            $additionalOptions = array_merge($additionalOptionstmp, $additionalOptionsOrder);
                        }
                    }
                    if ($quoteItem->getVendorId() && ($quoteItem->getProductType() !== Configurable::TYPE_CODE) && $this->helper->canHideVendorDetails()) {
                        $vendor = $this->_vendorFactory->create()->load($quoteItem->getVendorId());
                        $publicName = $vendor->getPublicName();
                        $additionalOptions[] = [
                            'code' => 'vendor_name',
                            'label' => 'Vendor',
                            'value' => $publicName
                        ];
                    }
                    if (!empty($additionalOptions)) {
                        $options = $orderItem->getProductOptions();
                        $options['additional_options'] = $additionalOptions;
                        $orderItem->setProductOptions($options);
                    }
                }
            }
        }
    }

    /**
     * @param $id
     * @param $quote
     * @return mixed|null
     */
    private function getQuoteItemById($id, $quote)
    {
        if (empty($this->quoteItems)) {
            /* @var  \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                //filter out config/bundle etc product
                $this->quoteItems[$item->getId()] = $item;
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }
}
