<?php

namespace Ced\CsMultiShipping\Plugin;

use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite as CedReaderComposite;;

class ReaderComposite
{
    protected $scopeConfig;

    protected $orderCollection;

    protected $shipmentRepository;

    protected $request;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderCollection = $orderCollection;
        $this->shipmentRepository = $shipmentRepository;
        $this->request = $request;
    }



    public function afterRead(CedReaderComposite $subject, $result)
    {
        if (isset($result['table']['quote_address']) && isset($result['table']['quote_address']['column']['shipping_method'])) {

            $result['table']['quote_address']['column']['shipping_method']['type'] = 'text';
            unset($result['table']['quote_address']['column']['shipping_method']['length']);

        }
        if (isset($result['table']['sales_order']) && isset($result['table']['sales_order']['column']['shipping_method'])) {

            $result['table']['sales_order']['column']['shipping_method']['type'] = 'text';
            unset($result['table']['sales_order']['column']['shipping_method']['length']);

        }
        if (isset($result['table']['quote_shipping_rate']) && isset($result['table']['quote_shipping_rate']['column']['code']) && isset($result['table']['quote_shipping_rate']['column']['method'])) {

            $result['table']['quote_shipping_rate']['column']['code']['type'] = 'text';
            unset($result['table']['quote_shipping_rate']['column']['code']['length']);

            $result['table']['quote_shipping_rate']['column']['method']['type'] = 'text';
            unset($result['table']['quote_shipping_rate']['column']['method']['length']);

        }

        return $result;
    }

}
