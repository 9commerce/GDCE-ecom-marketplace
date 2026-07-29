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

namespace Ced\CsMarketplace\Block\Vpayments;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class ListBlock
 * @package Ced\CsMarketplace\Block\Vpayments
 */
class ListBlock extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $_acl;

    /**
     * ListBlock constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\CsMarketplace\Helper\Acl $acl
    ) {
        $this->_acl = $acl;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $payments = [];
        if ($vendorId = $this->getVendorId()) {
            $payments = $this->getVendor()->getVendorPayments()->setOrder('created_at', 'DESC');
            $payments = $this->filterPayment($payments);
        }
        $this->setVpayments($payments);
    }


    /**
     * @param $paymentModel
     * @return mixed
     */
    public function filterPayment($paymentModel)
    {
        $filterParams = $this->session->getData('payment_filter');

        if (is_array($filterParams) && count($filterParams) > 0) {
            foreach ($filterParams as $field => $values) {
                if ($field == "__SID") continue;

                if (is_array($values)) {
                    if (isset($values['from']) && urldecode($values['from']) != "") {
                        $from = urldecode($values['from']);
                        if ($field == 'created_at')
                            $from = date("Y-m-d 00:00:00", strtotime($from));

                        $paymentModel->addFieldToFilter($field, ['gteq' => $from]);
                    }

                    if (isset($values['to']) && urldecode($values['to']) != "") {
                        $to = urldecode($values['to']);
                        if ($field == 'created_at')
                            $to = date("Y-m-d 59:59:59", strtotime($to));

                        $paymentModel->addFieldToFilter($field, ['lteq' => $to]);
                    }
                } else if (urldecode($values) != "") {
                    $filterCondition = ["like" => '%' . urldecode($values) . '%'];
                    if ($field == 'payment_method')
                        $filterCondition = ["in" => $this->_acl->getDefaultPaymentTypeValue(urldecode($values))];
                    $paymentModel->addFieldToFilter($field, $filterCondition);
                }

            }
        }
        return $paymentModel;
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
     * Return order view link
     *
     * @param $payment
     * @return String
     */
    public function getViewUrl($payment)
    {
        return $this->getUrl(
            '*/*/view',
            ['payment_id' => $payment->getId(), '_secure' => true, '_nosid' => true]
        );
    }

    /**
     * prepare list layout
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pagerblock = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Html\Pager', 'custom.pager');
        $pagerblock->setAvailableLimit([5 => 5, 10 => 10, 20 => 20, 'all' => 'all']);
        $pagerblock->setCollection($this->getVpayments());
        $this->setChild('pager', $pagerblock);
        $this->getVpayments()->load();
        return $this;
    }
}
