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

use Ced\CsMarketplace\Helper\Acl;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

class Vorders extends \Ced\CsMarketplace\Model\Vorders
{
    const STATE_PARTIALLY_PAID = 6;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Vorders constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Acl $marketplaceAclHelper
     * @param OrderFactory $orderFactory
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Acl $marketplaceAclHelper,
        OrderFactory $orderFactory,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct(
            $marketplaceAclHelper,
            $orderFactory,
            $collectionFactory,
            $dateTime,
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
     * Check vendor order Shipment action availability
     * @return bool
     */
    public function canShip()
    {
        if ($this->getOrder()->canShip()) {
            foreach ($this->getItemsCollection() as $item) {
                if ($item->getQtyToShip() > 0 && !$item->getIsVirtual()
                    && !$item->getLockedDoShip()
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param bool $incrementId
     * @param bool $viewMode
     * @return mixed
     */
    public function getOrder($incrementId = false, $viewMode = false)
    {
        $order = parent::getOrder($incrementId);

        if ($this->canShowShipmentButton() && $viewMode) {
            $order->setShippingAmount($this->getShippingAmount());
            $order->setBaseShippingAmount($this->getBaseShippingAmount());
            $order->setShippingDescription($this->getShippingDescription());
            $shipping = $this->getShippingAmount() + $order->getShippingTaxAmount();
            $baseShipping = $this->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
            $order->setShippingInclTax($shipping);
            $order->setBaseShippingInclTax($baseShipping);
        }
        return $order;
    }

    /**
     * Checks shipment allowed or not
     *
     * @return boolean
     */
    public function canShowShipmentButton()
    {
        if ($this->getCode()) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve vendor order states array
     * @return array
     */
    public function getStates()
    {
        if (self::$_states === null) {
            self::$_states = [
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled'),
                self::STATE_REFUND => __('Refund'),
                self::STATE_PARTIALLY_PAID => __('Partially Paid')
            ];
        }
        return self::$_states;
    }

    /**
     * Check vendor order Invoice action availability
     * @return bool
     */
    public function canInvoice()
    {
        if ($this->getOrder()->canInvoice()) {
            foreach ($this->getItemsCollection() as $item) {
                if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check vendor order Invoice action availability
     * @return bool
     */
    public function canCreditmemo()
    {
        if ($this->getOrder()->canCreditmemo()) {
            foreach ($this->getItemsCollection() as $item) {
                if ($item->getQtyInvoiced() > $item->getQtyRefunded()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check vendor order Invoice action availability
     * @return bool
     */
    public function isAdvanceOrder()
    {
        if ($this->getVordersMode() > 0) {
            return true;
        }
        if ($this->getVordersMode() == 0) {
            return false;
        }

        return false;
    }

    /**
     * @param $invoice
     * @return bool|\Magento\Framework\DataObject
     */
    public function getVorderByInvoice($invoice)
    {
        if ($invoice) {
            $incrementId = $invoice->getOrder()->getIncrementId();
            $vendorId = $this->customerSession->getVendorId();
            $attributes = ['vendor_id' => $vendorId, 'order_id' => $incrementId];
            return $this->loadByColumns($attributes);
        }
        return false;
    }

    /**
     * @param $shipment
     * @return bool|\Magento\Framework\DataObject
     */
    public function getVorderByShipment($shipment)
    {
        if ($shipment) {
            $incrementId = $shipment->getOrder()->getIncrementId();
            $vendorId = $this->customerSession->getVendorId();
            $attributes = ['vendor_id' => $vendorId, 'order_id' => $incrementId];
            return $this->loadByColumns($attributes);
        }
        return false;
    }

    /**
     * @param $creditmemo
     * @return bool|\Magento\Framework\DataObject
     */
    public function getVorderByCreditmemo($creditmemo)
    {
        if ($creditmemo) {
            $incrementId = $creditmemo->getOrder()->getIncrementId();
            $vendorId = $this->customerSession->getVendorId();
            $attributes = ['vendor_id' => $vendorId, 'order_id' => $incrementId];
            return $this->loadByColumns($attributes);
        }
        return false;
    }
}
