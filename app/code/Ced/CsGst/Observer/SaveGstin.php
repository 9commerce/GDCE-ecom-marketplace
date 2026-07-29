<?php


namespace Ced\CsGst\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;


class SaveGstin implements \Magento\Framework\Event\ObserverInterface
{
    const ADDRESS_TYPE = 'gstin_number';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * QuoteSubmitBefore constructor.
     * @param QuoteRepository $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }


    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        /** @var Order $order */
        $order = $observer->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        try {
            $this->orderBillingAddressFields($order, $quote);
            $this->orderShippingAddressFields($order, $quote);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param $order
     * @param $quote
     * @return $this
     */
    private function orderBillingAddressFields($order, $quote)
    {
        $order->getBillingAddress()->setData(
            self::ADDRESS_TYPE,
            $quote->getBillingAddress()->getData(self::ADDRESS_TYPE)
        )->save();

        return $this;
    }

    /**
     * @param $order
     * @param $quote
     * @return $this
     */
    private function orderShippingAddressFields($order, $quote)
    {
        if($order->getShippingAddress()) {
            $order->getShippingAddress()->setData(
                self::ADDRESS_TYPE,
                $quote->getShippingAddress()->getData(self::ADDRESS_TYPE)
            )->save();

            return $this;
        }
    }
}
