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

namespace Ced\CsOrder\Helper;

use Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor
     */
    protected $_vendorResource;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $address;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address
     */
    protected $_addressResource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $trackCollection;

    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $shipmentCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * @var \Magento\Framework\View\Context
     */
    protected $context;
    /**
     * @var \Magento\Sales\Model\Order\Invoice\CommentFactory
     */
    protected $invoiceCommentFactory;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\CommentFactory
     */
    protected $shipmentcommentFactory;
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\CommentFactory
     */
    protected $creditmemocommentFactory;
    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory
     */
    protected $csOrderCreditMemoCollectionFactory;
    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory
     */
    protected $csOrderInvoiceCollectionFactory;
    /**
     * @var \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $csOrderShipmentCollectionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param UrlInterface $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\AddressFactory $address
     * @param \Magento\Customer\Model\ResourceModel\Address $addressResource
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $shipmentCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollection
     * @param \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory
     * @param \Magento\Sales\Model\Order\Shipment\CommentFactory $shipmentcommentFactory
     * @param \Magento\Sales\Model\Order\Creditmemo\CommentFactory $creditmemocommentFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $csOrderInvoiceCollectionFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $csOrderShipmentCollectionFactory
     * @param \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $csOrderCreditMemoCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\AddressFactory $address,
        \Magento\Customer\Model\ResourceModel\Address $addressResource,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $shipmentCollection,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollection,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $shipmentcommentFactory,
        \Magento\Sales\Model\Order\Creditmemo\CommentFactory $creditmemocommentFactory,
        \Ced\CsOrder\Model\ResourceModel\Invoice\CollectionFactory $csOrderInvoiceCollectionFactory,
        \Ced\CsOrder\Model\ResourceModel\Shipment\CollectionFactory $csOrderShipmentCollectionFactory,
        \Ced\CsOrder\Model\ResourceModel\Creditmemo\CollectionFactory $csOrderCreditMemoCollectionFactory
    ) {
        parent::__construct($context);
        $this->url = $url;
        $this->_scopeConfigManager = $scopeConfig;
        $this->address = $address;
        $this->vendor = $vendor;
        $this->_vendorResource = $vendorResource;
        $this->_addressResource = $addressResource;
        $this->customerSession = $customerSession;
        $this->shipmentCollection = $shipmentCollection;
        $this->trackCollection = $trackCollection;
        $this->invoiceCommentFactory = $invoiceCommentFactory;
        $this->shipmentcommentFactory = $shipmentcommentFactory;
        $this->creditmemocommentFactory = $creditmemocommentFactory;
        $this->csOrderInvoiceCollectionFactory = $csOrderInvoiceCollectionFactory;
        $this->csOrderShipmentCollectionFactory = $csOrderShipmentCollectionFactory;
        $this->csOrderCreditMemoCollectionFactory = $csOrderCreditMemoCollectionFactory;
    }

    /**
     * @param $address
     * @return bool|string
     */
    public function getVendorNameByAddress($address)
    {
        if (is_numeric($address)) {
            $addressModel = $this->address->create();
            $this->_addressResource->load($addressModel, $address);
            if ($addressModel->getVendorId()) {
                $vendor = $this->vendor;
                $this->_vendorResource->load($vendor, $addressModel->getVendorId());
                return $vendor->getName();
            } else {
                return 'Admin';
            }
        } elseif ($address && $address->getId()) {
            $vendor = $this->vendor;
            $this->_vendorResource->load($vendor, $address->getVendorId());
            return $vendor->getName();
        } else {
            return false;
        }
    }

    /**
     * Check Vendor Log is enabled
     *
     * @return boolean
     */
    public function isVendorLogEnabled()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/vlogs/active', $this->getStore()->getId());
    }

    /**
     * Get current store
     * @return mixed
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId) {
            return $this->_scopeConfigManager->getStore($storeId);
        } else {
            return $this->_scopeConfigManager->getStore();
        }
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateInvoiceEnabled($vorder)
    {
        $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue(
            'ced_vorders/general/vorders_caninvoice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isSplitOrderEnable;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateShipmentEnabled($vorder)
    {
        if ($vorder->canShowShipmentButton()) {
            $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue(
                'ced_vorders/general/vorders_canshipment',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return $isSplitOrderEnable;
        }
        return false;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canCreateCreditmemoEnabled($vorder)
    {
        $isSplitOrderEnable = (boolean)$this->_scopeConfigManager->getValue(
            'ced_vorders/general/vorders_cancreditmemo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isSplitOrderEnable;
    }

    /**
     * @param $vorder
     * @return bool
     */
    public function canShowShipmentBlock($vorder)
    {
        if ($vorder->getCode() == null) {
            return false;
        }
        return true;
    }

    /**
     * Check Can distribute shipment
     *
     * @return boolean
     */
    public function isActive()
    {
        return (boolean)$this->_scopeConfigManager->getValue(
            'ced_vorders/general/vorders_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $order
     * @return bool
     */
    public function isShipmentCreated($order)
    {
        $isCreated = false;
        $vendorId = $this->customerSession->getVendorId();
        if (count($order->getShipmentsCollection())) {
            $shipmentId = $order->getShipmentsCollection()->getColumnValues('entity_id');

            $vShipments = $this->shipmentCollection->create()
                ->addFieldToFilter('shipment_id', ['in' => $shipmentId])
                ->addFieldToFilter('vendor_id', $vendorId);
            if (count($vShipments)) {
                $isCreated = true;
            }
        }
        return $isCreated;
    }

    /**
     * Shipping tracking popup URL getter
     *
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     */
    public function getTrackingPopupUrlBySalesModel($model)
    {
        $vendorId = $this->customerSession->getVendorId();
        if (count($model->getShipmentsCollection())) {
            $shipmentId = $model->getShipmentsCollection()->getColumnValues('entity_id');

            $vShipments = $this->shipmentCollection->create()
                ->addFieldToFilter('shipment_id', ['in' => $shipmentId])
                ->addFieldToFilter('vendor_id', $vendorId);
            if (count($vShipments)) {
                $model = $this->trackCollection->create()
                    ->addFieldToFilter('parent_id', $vShipments->getFirstItem()->getShipmentId());
                if (count($model)) {
                    $model = $model->getFirstItem();
                }
            }
        }
        if ($model instanceof \Magento\Sales\Model\Order) {
            return $this->_getTrackingUrl('order_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment) {
            return $this->_getTrackingUrl('ship_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment\Track) {
            return $this->_getTrackingUrl('track_id', $model, 'getEntityId');
        }
        return '';
    }

    /**
     * @param $key
     * @param $model
     * @param string $method
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
        $urlPart = "{$key}:{$model->{$method}()}:{$model->getProtectCode()}";

        $params = [
            '_scope' => $model->getStoreId(),
            '_nosid' => true,
            '_direct' => 'shipping/tracking/popup',
            '_query' => ['hash' => $this->urlEncoder->encode($urlPart)]
        ];

        return $this->url->getUrl('', $params);
    }

    /**
     * @param $invoice
     * @param $comment
     * @param false $notify
     * @param false $visibleOnFront
     * @return mixed
     */
    public function addInvoiceComment($invoice, $comment, $notify = false, $visibleOnFront = false)
    {
        $vendorId = $this->customerSession->getVendorId();
        if (!$comment instanceof \Magento\Sales\Model\Order\Invoice\Comment) {
            $comment = $this->invoiceCommentFactory->create()->setComment(
                $comment
            )->setIsCustomerNotified(
                $notify
            )->setIsVisibleOnFront(
                $visibleOnFront
            )->setVendorId($vendorId);
        }
        $comment->setInvoice($invoice)->setStoreId($invoice->getStoreId())->setParentId($invoice->getId());
        if (!$comment->getId()) {
            $invoice->getCommentsCollection()->addItem($comment);
        }
        $this->_hasDataChanges = true;
        return $invoice;
    }

    /**
     * @param $shipment
     * @param $comment
     * @param false $notify
     * @param false $visibleOnFront
     * @return mixed
     */
    public function addShipmentComment($shipment, $comment, $notify = false, $visibleOnFront = false)
    {
        $vendorId = $this->customerSession->getVendorId();
        if (!$comment instanceof \Magento\Sales\Model\Order\Shipment\Comment) {
            $comment = $this->shipmentcommentFactory->create()
                ->setComment($comment)
                ->setIsCustomerNotified($notify)
                ->setIsVisibleOnFront($visibleOnFront)
                ->setVendorId($vendorId);
        }
        $comment->setShipment($shipment)
            ->setParentId($shipment->getId())
            ->setStoreId($shipment->getStoreId());
        if (!$comment->getId()) {
            $shipment->getCommentsCollection()->addItem($comment);
        }
        $comments = $shipment->getComments();
        $comments[] = $comment;
        $shipment->setComments($comments);
        $this->_hasDataChanges = true;
        return $shipment;
    }

    /**
     * @param $creditmemo
     * @param $comment
     * @param false $notify
     * @param false $visibleOnFront
     * @return \Magento\Sales\Model\Order\Creditmemo\Comment|mixed
     */
    public function addCreditMemoComment($creditmemo, $comment, $notify = false, $visibleOnFront = false)
    {
        $vendorId = $this->customerSession->getVendorId();
        if (!$comment instanceof \Magento\Sales\Model\Order\Creditmemo\Comment) {
            $comment = $this->creditmemocommentFactory->create()->setComment(
                $comment
            )->setIsCustomerNotified(
                $notify
            )->setIsVisibleOnFront(
                $visibleOnFront
            )->setVendorId($vendorId);
        }
        $comment->setCreditmemo($creditmemo)->setParentId($creditmemo->getId())->setStoreId($creditmemo->getStoreId());
        $creditmemo->setComments(array_merge($creditmemo->getComments(), [$comment]));
        return $comment;
    }

    /**
     * @param $vid
     * @return array
     */
    public function getInvoiceIds($vid)
    {
        $vinvoiceCollection = $this->csOrderInvoiceCollectionFactory->create()->addFieldToFilter('vendor_id', $vid);
        return array_column($vinvoiceCollection->getData(), 'invoice_id');
    }

    /**
     * @param $vid
     * @return array
     */
    public function getShipmentIds($vid)
    {
        $vshipmentCollection = $this->csOrderShipmentCollectionFactory->create()->addFieldToFilter('vendor_id', $vid);
        return array_column($vshipmentCollection->getData(), 'shipment_id');
    }

    /**
     * @param $vid
     * @return array
     */
    public function getCreditMemoIds($vid)
    {
        $vcreditmemoCollection = $this->csOrderCreditMemoCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vid);
        return array_column($vcreditmemoCollection->getData(), 'creditmemo_id');
    }
}
