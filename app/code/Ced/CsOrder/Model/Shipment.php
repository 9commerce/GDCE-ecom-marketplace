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

namespace Ced\CsOrder\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Store\Model\ScopeInterface;

class Shipment extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * Payment states
     */
    const STATE_OPEN       = 1;
    const STATE_PAID       = 2;
    const STATE_CANCELED   = 3;
    const STATE_REFUND     = 4;
    const STATE_REFUNDED   = 5;

    const ORDER_NEW_STATUS=1;
    const ORDER_CANCEL_STATUS=3;

    protected $_items = null;

    protected static $_states;

    protected $_eventPrefix      = 'csorder_shipment';
    protected $_eventObject      = 'vshipment';
    public $_vendorstatus=null;
    protected $customerSession;
    protected $_dataHelper;
    protected $_aclHelper;
    protected $_storeManager;
    protected $regionFactory;

    /**
     * @var \Magento\Shipping\Model\Shipment\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Magento\User\Model\User\Session
     */
    protected $session;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region
     */
    protected $_regionResource;

    /**
     * Shipment constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Magento\User\Model\User $session
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Shipping\Model\Shipment\RequestFactory $requestFactory
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\ResourceModel\Region $regionResource,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\User\Model\User $session,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Shipping\Model\Shipment\RequestFactory $requestFactory,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->_regionResource = $regionResource;
        $this->customerSession = $customerSession;
        $this->vproducts = $vproducts;
        $this->session = $session;
        $this->requestFactory = $requestFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Ced\CsOrder\Model\ResourceModel\Shipment::class);
    }

    /**
     * @param $shipment
     * @return int
     */
    public function updateTotalqty($shipment)
    {
        $totalQty =  $this->getItemSubtotalByShipment($shipment);
        return $totalQty;
    }

    /**
     * @param $shipment
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemSubtotalByShipment($shipment)
    {
        $items = $shipment->getAllItems();
        $vendorId = $this->customerSession->getVendorId();
        $total = 0;
        foreach ($items as $_item) {
            if (!$_item->getProductId()) {
                continue;
            }
            $vendorIdProductId = $this->vproducts->getVendorIdByProduct($_item->getProductId());
            if ((is_object($_item->getOrderItem())
                    && $_item->getOrderItem()->getParentItem())
                    || $vendorIdProductId!=$vendorId) {
                continue;
            }
            $total += $_item->getQty();
        }
        return $total;
    }

    /**
     * @param Magento\Sales\Model\Order\Shipment $orderShipment
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function requestToShipment(Magento\Sales\Model\Order\Shipment $orderShipment)
    {
        $admin = $this->session;
        $order = $orderShipment->getOrder();
        $address = $order->getShippingAddress();
        $shippingMethod = $order->getShippingMethod(true);
        $shipmentStoreId = $orderShipment->getStoreId();
        $shipmentCarrier = $order->getShippingCarrier();
        $baseCurrencyCode = $this->_storeManager->getStore($shipmentStoreId)->getBaseCurrencyCode();
        if (!$shipmentCarrier) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __(
                    'Invalid carrier:  %message',
                    ['message' => implode(',', $shippingMethod->getCarrierCode())]
                )
            );
        }
        $shipperRegionCode =$this->_scopeConfig->getValue(self::XML_PATH_STORE_REGION_ID, $shipmentStoreId);
        if (is_numeric($shipperRegionCode)) {
            $regionObj = $this->regionFactory->create();
            $this->_regionResource->load($regionObj, $shipperRegionCode);
            if ($regionObj->getId()) {
                $shipperRegionCode = $regionObj->getCode();
            }
        }

        $regionObj2 = $this->regionFactory->create();
        $this->_regionResource->load($regionObj2, $address->getRegionId())->getCode();
        if ($regionObj2->getId()) {
            $recipientRegionCode = $regionObj2->getCode();
        }
        $originStreet1 = $this->_scopeConfig->getValue(self::XML_PATH_STORE_ADDRESS1, ScopeInterface::SCOPE_STORE);
        $originStreet2 = $this->_scopeConfig->getValue(self::XML_PATH_STORE_ADDRESS2, ScopeInterface::SCOPE_STORE);
        $storeInfo = new \Magento\Framework\DataObject(
            $this->_scopeConfig->getValue('general/store_information', ScopeInterface::SCOPE_STORE)
        );

        if (!$admin->getFirstname() || !$admin->getLastname() || !$storeInfo->getName() || !$storeInfo->getPhone()
            || !$originStreet1 || !$this->_scopeConfig->getValue(self::XML_PATH_STORE_CITY, $shipmentStoreId)
            || !$shipperRegionCode || !$this->_scopeConfig->getValue(self::XML_PATH_STORE_ZIP, $shipmentStoreId)
            || !$this->_scopeConfig->getValue(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        ) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Insufficient information to create shipping label(s).
                Please verify your Store Information and Shipping Settings.')
            );
        }

        $request = $this->requestFactory->create();
        $request->setOrderShipment($orderShipment);
        $request->setShipperContactPersonName($admin->getName());
        $request->setShipperContactPersonFirstName($admin->getFirstname());
        $request->setShipperContactPersonLastName($admin->getLastname());
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($admin->getEmail());
        $request->setShipperAddressStreet(trim($originStreet1 . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet1);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity($this->_scopeConfig->getValue(self::XML_PATH_STORE_CITY, $shipmentStoreId));
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode(
            $this->_scopeConfig->getValue(self::XML_PATH_STORE_ZIP, $shipmentStoreId)
        );
        $request->setShipperAddressCountryCode(
            $this->_scopeConfig->getValue(self::XML_PATH_STORE_COUNTRY_ID, $shipmentStoreId)
        );
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreet1() . ' ' . $address->getStreet2()));
        $request->setRecipientAddressStreet1($address->getStreet1());
        $request->setRecipientAddressStreet2($address->getStreet2());
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode());
        $request->setRecipientAddressRegionCode($recipientRegionCode);
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setPackages($orderShipment->getPackages());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($shipmentStoreId);
        return $shipmentCarrier->requestToShipment($request);
    }
}
