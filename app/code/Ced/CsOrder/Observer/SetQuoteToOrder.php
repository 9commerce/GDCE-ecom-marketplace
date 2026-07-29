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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetQuoteToOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializer;

    /**
     * SetQuoteToOrder constructor.
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(\Magento\Framework\Serialize\SerializerInterface $serializer)
    {
        $this->_serializer = $serializer;
    }

    /**
     * Set vendor name and url to product incart
     * @param $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote_items = $quote->getItemsCollection();
        foreach ($quote_items as $quoteItem) {
            if (!empty($quoteItem->getOptionByCode('additional_options'))) {
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                $orderItem = $observer->getEvent()->getOrderItem();
                $options = $orderItem->getProductOptions();
                $options['additional_options'] = $this->_serializer->unserialize($additionalOptions->getValue());
                $orderItem->setProductOptions($options);
            }
        }
    }
}
