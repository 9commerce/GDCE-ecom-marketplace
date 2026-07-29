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

namespace Ced\CsMarketplace\Block\Vpayments\ListBlock;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class Request
 * @package Ced\CsMarketplace\Block\Vpayments\ListBlock
 */
class Request extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $acl;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csMarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $requested;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_acl;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_csMarketplaceHelper;

    /**
     * Request constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Ced\CsMarketplace\Helper\Data $csMarketplaceHelper
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $requested
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Ced\CsMarketplace\Helper\Data $csMarketplaceHelper,
        \Ced\CsMarketplace\Model\Vpayment\Requested $requested
    ) {
        $this->_session = $customerSession;
        $this->_acl = $acl;
        $this->_csMarketplaceHelper = $csMarketplaceHelper;
        $this->requested = $requested;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $pendingPayments = array();
        if ($vendorId = $this->getVendorId()) {
            $pendingPayments = $this->getVendor()->getAssociatedOrders()
                ->addFieldToFilter('order_payment_state', array('in' => [\Magento\Sales\Model\Order\Invoice::STATE_PAID,
                    \Ced\CsOrder\Model\Invoice::STATE_PARTIALLY_PAID]))
                ->addFieldToFilter('payment_state', array('eq' => \Ced\CsMarketplace\Model\Vorders::STATE_OPEN))
                ->setOrder('created_at', 'ASC');
            $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
            $order_total = $this->_csMarketplaceHelper->getTableKey('order_total');
            $shop_commission_fee = $this->_csMarketplaceHelper->getTableKey('shop_commission_fee');
            $pendingPayments->getSelect()
                ->columns(
                    array('net_vendor_earn' => new \Zend_Db_Expr(
                        "({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})")
                    )
                );

            $pendingPayments = $this->filterPayment($pendingPayments);
        }
        $this->setPendingVpayments($pendingPayments);
    }

    /**
     * @param $paymentRequest
     * @return mixed
     */
    public function filterPayment($paymentRequest)
    {
        $requestFilter = $this->_session->getData('payment_request_filter');

        if (is_array($requestFilter)) {
            foreach ($requestFilter as $field => $values) {
                if ($field == "__SID") continue;

                if (is_array($values)) {
                    if (!empty($values['from']) && urldecode($values['from']) != "") {
                        $fromDate = urldecode($values['from']);
                        if ($field == 'created_at')
                            $fromDate = date("Y-m-d 00:00:00", strtotime($fromDate));

                        $paymentRequest->addFieldToFilter($field, ['gteq' => $fromDate]);
                    }

                    if (!empty($values['to']) && urldecode($values['to']) != "") {
                        $toDate = urldecode($values['to']);
                        if ($field == 'created_at')
                            $toDate = date("Y-m-d 59:59:59", strtotime($toDate));

                        $paymentRequest->addFieldToFilter($field, ['lteq' => $toDate]);
                    }
                } else if (urldecode($values) != "") {
                    $condition = ($field == 'payment_method') ?
                        ["in" => $this->_acl->getDefaultPaymentTypeValue(urldecode($values))] :
                        ["like" => '%' . urldecode($values) . '%'];

                    $paymentRequest->addFieldToFilter($field, $condition);
                }

            }
        }

        return $paymentRequest;
    }

    /**
     * @return bool
     */
    public function cancelledTransaction()
    {
        if ($vendorId = $this->getVendorId()) {
            $requested = $this->requested->getCollection()->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('status',
                    array('neq' => \Ced\CsMarketplace\Model\Vpayment\Requested::PAYMENT_STATUS_REQUESTED))->getData();
            return $requested;
        } else {
            return false;
        }
    }

    /**
     * return the pager
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * return Back Url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', ['_secure' => true, '_nosid' => true]);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Html\Pager', 'custom.pager');
        $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
        $pager->setCollection($this->getPendingVpayments());
        $this->setChild('pager', $pager);
        $this->getPendingVpayments()->load();
        return $this;
    }

}
