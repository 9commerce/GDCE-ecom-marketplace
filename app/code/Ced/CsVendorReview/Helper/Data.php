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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Customer\Model\Session $session,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection,
        \Magento\Framework\Registry $registry
    ) {
        $this->orderCollection = $orderCollection;
        $this->session = $session;
        $this->vproductsCollection = $vproductsCollection;
        $this->registry = $registry;
        $this->_remoteAddress = $context->getRemoteAddress();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    public function getRemoteAddress()
    {
        return $this->_remoteAddress;
    }

    /**
     * @return mixed
     */
    public function isCustomerAllowed()
    {
        return $this->scopeConfig->getValue(
            'ced_csmarketplace/vendorreview/purchase_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function checkVendorProduct()
    {
        $collection = $this->orderCollection->create()->addFieldToFilter('customer_id', $this->getCustomerId());
        $sales_order_item = $collection->getTable('sales_order_item');
        $collection->getSelect()->join(
            $sales_order_item,
            $sales_order_item.'.order_id=`main_table`.entity_id',
            [
                'product_ids' => new \Zend_Db_Expr('group_concat('.$sales_order_item.'.product_id SEPARATOR ",")')
            ]
        )->group('main_table.customer_id');

        $productIds = array_values(array_unique(explode(',', $collection->getFirstItem()->getProductIds()??'')));
        $vendorProductIds = array_column($this->getVendorProducts($this->getVendorId()), 'product_id');
        $result = array_intersect($productIds, $vendorProductIds);

        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @return int|null
     */
    protected function getCustomerId()
    {
        return $this->session->getCustomerId();
    }

    /**
     * @param $vendor_id
     * @return mixed
     */
    public function getVendorProducts($vendor_id)
    {
        $products = $this->vproductsCollection->create()
            ->addFieldToFilter('vendor_id', $vendor_id)
            ->addFieldToFilter('check_status', 1)
            ->addFieldToSelect('product_id');
        return $products->getData();
    }

    /**
     * @return mixed
     */
    protected function getVendorId()
    {
        return $this->registry->registry('current_vendor')->getId();
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @return mixed
     */
    public function isReviewAllowed()
    {
        return $this->scopeConfig->getValue(
            'ced_csmarketplace/vendorreview/product_page_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
