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

namespace Ced\CsMultiShipping\Plugin;

class Order
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Framework\App\Request\Http $request
     */
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

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param \Closure $proceed
     * @param false $asObject
     * @return array|\Magento\Framework\DataObject|mixed|string|string[]|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetShippingMethod(
        \Magento\Sales\Model\Order $subject,
        \Closure $proceed,
        $asObject = false
    ) {
        $shipmentId = $this->request->getParam('shipment_id');
        if ($shipmentId) {
            $shipmentData = $this->shipmentRepository->get($shipmentId);
            $orderCollectionData = $this->orderCollection->create()
                ->addFieldToFilter('entity_id', $shipmentData->getOrderId());
            $shippingMethod = $orderCollectionData->getFirstItem()->getData('shipping_method');
            $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
            $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
            if (strpos($shippingMethod, '~')!==false) {
                $shippingMethod = explode('~', $shippingMethod);
                $shippingMethod = $shippingMethod[0];
            }
            $shippingMethodExplode = explode(':', $shippingMethod);

            if (isset($shippingMethodExplode[0])) {
                $shippingMethod = $shippingMethodExplode[0];
            }
            $isMultishippingEnable = $this->scopeConfig->getValue(
                'ced_csmultishipping/general/activation',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($isMultishippingEnable) {
                if (!$asObject || !$shippingMethod) {
                    return $shippingMethod;
                } else {
                    list($carrierCode, $method) = explode('_', $shippingMethod, 2);
                    return new \Magento\Framework\DataObject(['carrier_code' => $carrierCode, 'method' => $method]);
                }
            }
        }

        return $proceed($asObject);
    }
}
