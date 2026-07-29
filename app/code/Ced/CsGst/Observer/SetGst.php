<?php

namespace Ced\CsGst\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;


class SetGst implements ObserverInterface
{
    private $quoteItems = [];
    private $quote = null;
    private $order = null;
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Catalog\Model\ProductFactory $ProductFactory,
        \Magento\Catalog\Model\ResourceModel\Product $CatalogResource
    ){
        $this->state = $state;
        $this->jsonSerializer = $jsonSerializer;
        $this->productFactory = $ProductFactory;
        $this->productResource = $CatalogResource;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->state->getAreaCode() !== 'adminhtml' ) {
            $this->quote = $observer->getQuote();
            $this->order = $observer->getOrder();
            foreach ($this->order->getItems() as $orderItem) {
                if ($quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId(), $this->quote)) {
                    $additionalOptions = [];
                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        $additionalOptions = $additionalOptionsQuote->getValue();

                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptionstmp = $this->jsonSerializer->unserialize($additionalOptionsQuote->getValue());
                            $additionalOptionsOrdertmp = $this->jsonSerializer->unserialize($additionalOptionsOrder);
                            $additionalOptions = array_merge($additionalOptionstmp, $additionalOptionsOrdertmp);
                        }
                    }
                    if (($quoteItem->getProductType() !== Configurable::TYPE_CODE)){
                        $hsn = '';
                        $catalog = $this->productFactory->create();
                        $this->productResource->load($catalog,$quoteItem->getProduct()->getEntityId());
                        $hsn = $catalog->getHsn();
                        if ($hsn !== null && $hsn) {
                            $additionalOptions[] = [
                                'code'  => 'hsncode',
                                'label'  => 'HSN Code',
                                'value' => $hsn
                            ];
                        }else{
                            continue;
                        }
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
