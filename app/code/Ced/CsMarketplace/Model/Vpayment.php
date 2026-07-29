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

namespace Ced\CsMarketplace\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Ced\CsMarketplace\Helper\Data;


/**
 * Class Vpayment
 * @package Ced\CsMarketplace\Model
 */
class Vpayment extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    const TRANSACTION_TYPE_CREDIT = 0;
    const TRANSACTION_TYPE_DEBIT = 1;

    const PAYMENT_STATUS_OPEN       = 1;
    const PAYMENT_STATUS_PAID       = 2;
    const PAYMENT_STATUS_CANCELED = 3;
    const PAYMENT_STATUS_REFUND   = 4;
    const PAYMENT_STATUS_REFUNDED = 5;

    /**
     * @var
     */
    protected static $_states;

    /**
     * @var
     */
    protected static $_statuses;

    /**
     * @var string
     */
    protected $_eventPrefix = 'csmarketplace_vpayments';

    /**
     * @var string
     */
    protected $_eventObject = 'vpayment';

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $_vOrders;

    /**
     * @var Data
     */
    protected $_marketplaceDataHelper;

    /**
     * Vpayment constructor.
     * @param \Ced\CsMarketplace\Model\Vorders $vOrders
     * @param Data $marketplaceDataHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Vorders $vOrders,
        Data $marketplaceDataHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_vOrders = $vOrders;
        $this->_marketplaceDataHelper = $marketplaceDataHelper;
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vpayment');
    }

    /**
     * Retrieve vendor payment type array
     *
     * @return array
     */
    public function getStates()
    {
        if ((self::$_states) === null) {
            self::$_states = array(
                self::TRANSACTION_TYPE_CREDIT => __('Credit'),
                self::TRANSACTION_TYPE_DEBIT  => __('Debit'),
            );
        }
        return self::$_states;
    }

    /**
     * Retrieve vendor payment open status
     *
     * @return int $openStatus
     */
    public function getOpenStatus()
    {
        if (!$this->_marketplaceDataHelper->getStoreConfig('ced_vpayments/general/payment_approval')) {
            return $this->getConfirmStatus();
        }

        $openStatus = self::PAYMENT_STATUS_OPEN;
        if ($this->getData('transaction_type')) {
            switch ($this->getData('transaction_type')) {
                case self::TRANSACTION_TYPE_DEBIT :
                    $openStatus = self::PAYMENT_STATUS_REFUND;
                    break;
                case self::TRANSACTION_TYPE_CREDIT :
                default :
                    $openStatus = self::PAYMENT_STATUS_OPEN;
                    break;
            }
        }
        return $openStatus;
    }

    /**
     * Retrieve vendor payment confirm status
     *
     * @return int $confirmStatus
     */
    public function getConfirmStatus()
    {
        $confirmStatus = self::PAYMENT_STATUS_PAID;
        if ($this->getData('transaction_type')) {
            switch ($this->getData('transaction_type')) {
                case self::TRANSACTION_TYPE_DEBIT : $confirmStatus = self::PAYMENT_STATUS_REFUNDED;
                    break;
                case self::TRANSACTION_TYPE_CREDIT :
                default : $confirmStatus = self::PAYMENT_STATUS_PAID;
                    break;
            }
        }
        return $confirmStatus;
    }

    /**
     * Retrieve vendor payment status array
     *
     * @return array
     */
    public function getStatuses()
    {
        if ((self::$_statuses) === null) {
            self::$_statuses = array(
                self::PAYMENT_STATUS_OPEN       => __('Pending'),
                self::PAYMENT_STATUS_PAID       => __('Paid'),
                self::PAYMENT_STATUS_CANCELED   => __('Canceled'),
                self::PAYMENT_STATUS_REFUND     => __('Refund'),
                self::PAYMENT_STATUS_REFUNDED   => __('Refunded'),
            );
        }
        return self::$_statuses;
    }

    /**
     * Retrieve product current balance by vendor Id
     * @param $vendorId
     * @return array
     */
    public function getCurrentBalance($vendorId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setOrder('entity_id', 'desc');

        return (count($collection) > 0) ? [$collection->getFirstItem()->getBalance(),
            $collection->getFirstItem()->getBaseBalance()] : [0,0];
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveOrders($data = array())
    {
        if (count($data) > 0 && isset($data['amount_desc'])) {
            $state = $this->getStateByType($data);
            $amount_desc = json_decode($data['amount_desc'], true);

            if (is_array($amount_desc) && count($amount_desc) > 0 && isset($data['vendor_id']) ) {
                foreach ($amount_desc as $orderId=>$amount) {
                    $model = $this->_vOrders->loadByField(['vendor_id','order_id'],
                        [$data['vendor_id'],trim($orderId)]);
                    if ($model->getVendorId() == $data['vendor_id'] && $model->getOrderId() == trim($orderId)) {
                        $model->setPaymentState($state)->save();
                    }
                }
            }
        }
    }

    /**
     * @param array $data
     * @return int
     */
    public function getStateByType($data = [])
    {
        $type = isset($data['transaction_type']) ? $data['transaction_type'] : self::TRANSACTION_TYPE_CREDIT;
        switch ($type) {
            case self::TRANSACTION_TYPE_DEBIT :
                return Vorders::STATE_REFUNDED;

            case self::TRANSACTION_TYPE_CREDIT :
            default :
                return Vorders::STATE_PAID;
        }
    }
}
