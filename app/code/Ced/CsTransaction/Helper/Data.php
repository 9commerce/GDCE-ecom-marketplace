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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Helper;

use Ced\CsMarketplace\Model\Vpayment;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_csMarketplaceHelper;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory
     */
    protected $_vitemsCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $_vordersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vorders
     */
    protected $_vordersResource;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vitemsCollectionFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory $vitemsCollectionFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vorders $vordersResource
    ) {
        parent::__construct($context);
        $this->_csMarketplaceHelper = $helperData;
        $this->_vitemsCollectionFactory = $vitemsCollectionFactory;
        $this->_vordersFactory = $vordersFactory;
        $this->_vordersResource = $vordersResource;
    }

    /**
     * @param $vorder
     * @param $type
     * @return string
     */
    public function getAvailableShipping($vorder, $type)
    {
        $shippingAmount = '';
        if ($type == Vpayment::TRANSACTION_TYPE_DEBIT) {
            if ($vorder->getShippingPaid() > $vorder->getShippingRefunded()) {
                $shippingAmount = $vorder->getShippingPaid() - $vorder->getShippingRefunded();
            } elseif ($vorder->getShippingPaid() == $vorder->getShippingRefunded() && $vorder->getShippingPaid() != 0) {
                $shippingAmount = 'Refunded';
            } else {
                $shippingAmount = 'N/A';
            }
        } elseif ($type == Vpayment::TRANSACTION_TYPE_CREDIT) {
            if ($vorder->getShippingPaid() == 0) {
                $shippingAmount = $vorder->getShippingAmount() + $vorder->getShippingRefunded();
            } elseif ($vorder->getShippingPaid() > 0 && $vorder->getShippingAmount() != 0) {
                $shippingAmount = 'Paid';
            } else {
                $shippingAmount = 'N/A';
            }
        }
        return $shippingAmount;
    }

    /**
     * @param $orderId
     * @param $vendorId
     * @param null $itemId
     * @return string
     */
    public function getTotalEarn($orderId, $vendorId, $itemId = null)
    {
        $collection = $this->_vitemsCollectionFactory->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('parent_id', ['eq' => $orderId]);

        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
        $item_commission = $this->_csMarketplaceHelper->getTableKey('item_commission');

        $collection->addFieldToFilter('qty_ready_to_pay', ['gt' => 0]);

        $collection->getSelect()
            ->columns(['net_vendor_earn' => new \Zend_Db_Expr("sum({$main_table}.{$item_fee})")]);
        $collection->getSelect()
            ->columns(['commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission})")]);

        $earn = $collection->getFirstItem()->getNetVendorEarn();

        $vorder = $this->_vordersFactory->create();
        $this->_vordersResource->load($vorder, $orderId);

        $shippingAmount = $this->getAvailableShipping($vorder, 'credit');
        $totalEarn = (float)$earn + (float)$shippingAmount;

        return $totalEarn;
    }
}
