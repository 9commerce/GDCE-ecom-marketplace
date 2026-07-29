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

namespace Ced\CsMarketplace\Helper;

use Ced\CsMarketplace\Model\Vorders;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
use Ced\CsMarketplace\Model\Vendor;

/**
 * Class Mail
 * @package Ced\CsMarketplace\Helper
 */
class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ACCOUNT_EMAIL_IDENTITY = 'ced_csmarketplace/vendor/email_identity';
    const XML_PATH_ACCOUNT_CONFIRMED_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/account_confirmed_template';
    const XML_PATH_ACCOUNT_REJECTED_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/account_rejected_template';
    const XML_PATH_ACCOUNT_DELETED_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/account_deleted_template';

    const XML_PATH_SHOP_ENABLED_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/shop_enabled_template';
    const XML_PATH_SHOP_DISABLED_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/shop_disabled_template';

    const XML_PATH_PRODUCT_EMAIL_IDENTITY = 'ced_vproducts/general/email_identity';
    const XML_PATH_PRODUCT_CONFIRMED_EMAIL_TEMPLATE = 'ced_vproducts/general/product_approved_template';
    const XML_PATH_PRODUCT_REJECTED_EMAIL_TEMPLATE = 'ced_vproducts/general/product_rejected_template';
    const XML_PATH_PRODUCT_DELETED_EMAIL_TEMPLATE = 'ced_vproducts/general/product_deleted_template';

    const XML_PATH_ORDER_EMAIL_IDENTITY = 'ced_vorders/general/email_identity';
    const XML_PATH_ORDER_NEW_EMAIL_TEMPLATE = 'ced_vorders/general/order_new_template';
    const XML_PATH_ORDER_CANCEL_EMAIL_TEMPLATE = 'ced_vorders/general/order_cancel_template';

    const XML_PATH_ACCOUNT_EMAIL_TO_ADMIN_TEMPLATE = 'ced_csmarketplace/vendor/account_mail_to_admin_template';
    const XML_PATH_PRODUCT_EMAIL_TO_ADMIN_TEMPLATE = 'ced_csmarketplace/vendor/product_mail_to_admin_template';

    const XML_PATH_SELLER_TRANSACTION_TEMPLATE = 'ced_vorders/general/seller_transaction_template';

    const XML_PATH_ACCOUNT_VERIFY_EMAIL_TEMPLATE = 'ced_csmarketplace/vendor/verified_email_template';

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproductsCollection;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var
     */
    protected $_scopeConfigManager;

    /**
     * Mail constructor.
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Backend\Model\UrlInterface $urlInterface
     */
    public function __construct(
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Backend\Model\UrlInterface $urlInterface
    ) {
        $this->_scopeConfigManager = $context->getScopeConfig();
        $this->paymentHelper = $paymentHelper;
        $this->addressRenderer = $addressRenderer;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->vendorFactory = $vendorFactory;
        $this->vordersFactory = $vordersFactory;
        $this->registry = $registry;
        $this->vproductsFactory = $vproductsFactory;
        $this->productFactory = $productFactory;
        $this->vproductsCollection = $vproductsCollection;
        $this->transportBuilder = $transportBuilder;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * Send account status change email to vendor
     *
     * @param $status
     * @param  string $backUrl
     * @param $vendor
     * @param  string $storeId
     * @return bool|Mail
     */
    public function sendAccountEmail($status, $vendor, $backUrl = '',$storeId = null)
    {
        $types = [
            Vendor::VENDOR_APPROVED_STATUS => self::XML_PATH_ACCOUNT_CONFIRMED_EMAIL_TEMPLATE,
            Vendor::VENDOR_DISAPPROVED_STATUS => self::XML_PATH_ACCOUNT_REJECTED_EMAIL_TEMPLATE,
            Vendor::VENDOR_DELETED_STATUS => self::XML_PATH_ACCOUNT_DELETED_EMAIL_TEMPLATE,
        ];
        if (!isset($types[$status]))
            return false;

        if ($storeId === null) {
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
        }

        $this->_sendEmailTemplate(
            $types[$status],
            self::XML_PATH_ACCOUNT_EMAIL_IDENTITY,
            ['vendor' => $vendor, 'back_url' => $backUrl],
            $storeId
        );
        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param $template
     * @param $sender
     * @param  array $templateParams
     * @param  int|null $storeId
     * @return Mail
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
    {
        try {
            $vendor = $templateParams['vendor'];
            $transportBuilder = $this->transportBuilder;
            $transportBuilder->addTo($vendor->getEmail(), $vendor->getName());
            $transportBuilder->setTemplateIdentifier($this->getStoreConfig($template, $storeId));
            $transportBuilder->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            );

            $transportBuilder->setTemplateVars($templateParams);
            $transportBuilder->setFrom($this->getStoreConfig($sender, $storeId));
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
        }
        return $this;
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfig($path, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }

    /**
     * Send shop enable/disable to vendor
     *
     * @param $status
     * @param  string $backUrl
     * @param $vendor
     * @param  string $storeId
     * @return bool|Mail
     */
    public function sendShopEmail($status, $vendor, $backUrl = '',$storeId = '0')
    {
        $types = array(
            \Ced\CsMarketplace\Model\Vshop::ENABLED => self::XML_PATH_SHOP_ENABLED_EMAIL_TEMPLATE,
            \Ced\CsMarketplace\Model\Vshop::DISABLED => self::XML_PATH_SHOP_DISABLED_EMAIL_TEMPLATE,
        );
        if (!isset($types[$status])) {
            return false;
        }

        if (!$storeId) {
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
        }

        $this->_sendEmailTemplate(
            $types[$status],
            self::XML_PATH_ACCOUNT_EMAIL_IDENTITY,
            array('vendor' => $vendor, 'back_url' => $backUrl),
            $storeId
        );
        return $this;
    }

    /**
     * Send order notification email to vendor
     *
     * @param \Magento\Sales\Model\Order $order
     * @param $type
     * @param $vendorId
     * @param $vorder
     */
    public function sendOrderEmail(\Magento\Sales\Model\Order $order, $type, $vendorId, $vorder)
    {
        $types = array(
            Vorders::ORDER_NEW_STATUS => self::XML_PATH_ORDER_NEW_EMAIL_TEMPLATE,
            Vorders::ORDER_CANCEL_STATUS => self::XML_PATH_ORDER_CANCEL_EMAIL_TEMPLATE,
        );
        if (!isset($types[$type])) {
            return;
        }
        $storeId = $order->getStore()->getId();
        if ($type == Vorders::ORDER_NEW_STATUS) {
            if (!$this->canSendNewOrderEmail($storeId)) {
                return;
            }
        }
        if ($type == Vorders::ORDER_CANCEL_STATUS) {
            if (!$this->canSendCancelOrderEmail($storeId)) {
                return;
            }
        }

        $vendor = $this->vendorFactory->create()->load($vendorId);
        $vorder = $this->vordersFactory->create()->loadByField(
            array('order_id', 'vendor_id'),
            array($order->getIncrementId(), $vendorId)
        );
        if ($this->registry->registry('current_order') != '') {
            $this->registry->unregister('current_order');
        }

        if ($this->registry->registry('current_vorder') != '') {
            $this->registry->unregister('current_vorder');
        }
        $this->registry->register('current_order', $order);
        $this->registry->register('current_vorder', $vorder);

        $this->_sendEmailTemplate(
            $types[$type],
            self::XML_PATH_ORDER_EMAIL_IDENTITY,
            array(
                'vendor' => $vendor, 'vendor_id'=>$vendorId,'order' => $order, 'order_items'=>$order->getAllItems(), 'order_id'=>$order->getId(),'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'showCustomerDetails' =>$this->canHideCustomerDetails()
            ),
            $storeId
        );
    }

    /**
     * Can send new order notification email
     *
     * @param  int $storeId
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canSendNewOrderEmail($storeId)
    {
        return $this->getStoreConfig(
            'ced_vorders/general/order_email_enable',
            $this->storeManager->getStore(null)->getStoreId()
        );
    }

    /**
     * Can send new order notification email
     *
     * @param  int $storeId
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canSendCancelOrderEmail($storeId)
    {
        return $this->getStoreConfig(
            'ced_vorders/general/order_cancel_email_enable',
            $this->storeManager->getStore(null)->getStoreId()
        );
    }

    /**
     * @param $order
     * @return mixed
     */
    protected function getPaymentHtml($order)
    {
        $storeId = $this->storeManager->getStore(null)->getId();
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }

    /**
     * @param $order
     * @return |null |null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    /*set up a Mail functionality For Product Delete From Admin Panel*/

    /**
     * Send product status change notification email to vendor
     *
     * @param $ids
     * @param $status
     */
    public function sendProductNotificationEmail($ids, $status)
    {
        $types = array(
            Vproducts::APPROVED_STATUS => self::XML_PATH_PRODUCT_CONFIRMED_EMAIL_TEMPLATE,
            Vproducts::NOT_APPROVED_STATUS => self::XML_PATH_PRODUCT_REJECTED_EMAIL_TEMPLATE,
            Vproducts::DELETED_STATUS => self::XML_PATH_PRODUCT_DELETED_EMAIL_TEMPLATE,
        );

        if (!isset($types[$status])) {
            return;
        }

        $vendorIds = array();
        foreach ($ids as $productId) {
            $vendorId = $this->vproductsFactory->create()->getVendorIdByProduct($productId);
            $vendorIds[$vendorId][] = $productId;
        }

        foreach ($vendorIds as $vendorId => $productIds) {
            $vendor = $this->vendorFactory->create()->load($vendorId);
            if (!$vendor->getId()) {
                continue;
            }
            $products = array();

            foreach ($productIds as $productId) {
                if ($status != Vproducts::DELETED_STATUS) {
                    $product = $this->productFactory->create()->load($productId);
                    if ($product && $product->getId()) {
                        $products[0][] = $product;
                    }
                }
                $products[1][$productId] =
                    $this->vproductsCollection->create()->addFieldToFilter('product_id', array('eq' => $productId))
                    ->getFirstItem();
            }
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
            $this->_sendEmailTemplate(
                $types[$status],
                self::XML_PATH_PRODUCT_EMAIL_IDENTITY,
                array('vendor' => $vendor, 'products' => $products),
                $storeId
            );
        }
    }

    /**
     * @param $status
     * @param $vendorIds
     */
    public function ProductDelete($status, $vendorIds)
    {
        $types = array(
            Vproducts::APPROVED_STATUS => self::XML_PATH_PRODUCT_CONFIRMED_EMAIL_TEMPLATE,
            Vproducts::NOT_APPROVED_STATUS => self::XML_PATH_PRODUCT_REJECTED_EMAIL_TEMPLATE,
            Vproducts::DELETED_STATUS => self::XML_PATH_PRODUCT_DELETED_EMAIL_TEMPLATE,
        );

        foreach ($vendorIds as $vendorId => $productIds) {

            $vendor = $this->vendorFactory->create()->load($vendorId);
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
            $this->_sendEmailTemplate(
                $types[$status],
                self::XML_PATH_PRODUCT_EMAIL_IDENTITY,
                array('vendor' => $vendor, 'products' => $vendorIds[$vendorId]),
                $storeId
            );
        }
    }

    /**
     * Send vendor account email to admin
     *
     * @param $vendor
     * @param string $storeId
     *
     * @return Mail
     */
    public function sendAccountEmailToAdmin($vendor, $storeId = null)
    {
        $template = self::XML_PATH_ACCOUNT_EMAIL_TO_ADMIN_TEMPLATE;
        if ($storeId === null) {
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
        }

        $admin['name'] = $this->getStoreConfig('trans_email/ident_general/name', $storeId);
        $admin['email'] = $this->getStoreConfig('trans_email/ident_general/email', $storeId);

        $vendor_url = $this->urlInterface->getUrl('csmarketplace/vendor/index');
        $this->_sendEmailTemplateToAdmin(
            $template,
            $admin,
            ['vendor' => $vendor, 'vendor_url' => $vendor_url],
            $storeId
        );

        return $this;
    }

    /**
     * Send corresponding email template to admin
     *
     * @param $template
     * @param $admin
     * @param  array $templateParams
     * @param  int|null $storeId
     * @return Mail
     */
    protected function _sendEmailTemplateToAdmin($template, $admin, $templateParams = array(), $storeId = null)
    {
        try {
            $transportBuilder = $this->transportBuilder;
            $transportBuilder->addTo($admin['email']);
            $transportBuilder->setTemplateIdentifier($this->getStoreConfig($template, $storeId));
            $transportBuilder->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ]
            );

            $transportBuilder->setTemplateVars($templateParams);
            $transportBuilder->setFrom($admin);
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
        }
        return $this;
    }

    /**
     * Send vendor transaction mail
     * @param $model
     */
    public function sendSellerTransactionEmail($model)
    {
        $type = self::XML_PATH_SELLER_TRANSACTION_TEMPLATE;
        $vendor = $this->vendorFactory->create()->load($model->getVendorId());
        $transactionType = "Credit";
        if ($model->getTransactionType() == 1) {
            $transactionType = "Debit";
        }

        $this->_sendEmailTemplate(
            $type,
            self::XML_PATH_ORDER_EMAIL_IDENTITY,
            array('vendor' => $vendor, 'transaction' => $model, 'transactionType' => $transactionType,'vendor_name' => $vendor->getName(), 'transaction_id' => $model->getId()),
            0
        );
    }

    /**
     *
     */
    public function sendProductMailToAdmin()
    {
        $pending_products = $this->vproductsFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('check_status', Vproducts::PENDING_STATUS)
            ->count();
        if ($pending_products >= 1) {
            $this->sendProductEmailToAdmin($pending_products);
        }
    }

    /**
     * Send vendor product email to admin
     *
     * @param $count
     * @param string $storeId
     * @return Mail
     */
    public function sendProductEmailToAdmin($count, $storeId = null)
    {
        $template = self::XML_PATH_PRODUCT_EMAIL_TO_ADMIN_TEMPLATE;
        $admin['name'] = $this->getStoreConfig('trans_email/ident_general/name', $storeId);
        $admin['email'] = $this->getStoreConfig('trans_email/ident_general/email', $storeId);
        $this->_sendEmailTemplateToAdmin(
            $template,
            $admin,
            ['count' => $count],
            $storeId
        );

        return $this;
    }

    /**
     * @return mixed
     */
    public function canHideCustomerDetails(){
        return $this->getStoreConfig(
            'ced_csmarketplace/general/hide_customer_to_vendor',
            $this->storeManager->getStore(null)->getStoreId()
        )?true:'';
    }

    /**
     * Send Customer Confirmation Email
     *
     * @param $vendor
     * @param  string $storeId
     * @return bool|Mail
     */
    public function sendConfirmationEmail($vendor, $storeId = null)
    {
        if ($storeId === null) {
            $customer = $this->customerFactory->create()->load($vendor->getCustomerId());
            $storeId = $customer->getStoreId();
        }

        $this->_sendEmailTemplate(
            self::XML_PATH_ACCOUNT_VERIFY_EMAIL_TEMPLATE,
            self::XML_PATH_ACCOUNT_EMAIL_IDENTITY,
            ['vendor' => $vendor],
            $storeId
        );
        return $this;
    }
}