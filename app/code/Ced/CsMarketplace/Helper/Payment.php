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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class Payment
 * @package Ced\CsMarketplace\Helper
 */
class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Contains current collection
     *
     * @var string
     */
    protected $_list = null;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_path = 'export';

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment
     */
    protected $vpayment;

    /**
     * @var Acl
     */
    protected $_acl;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $session;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Payment constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\Session $session
     * @param Acl $acl
     * @param Filesystem $filesystem
     * @param \Ced\CsMarketplace\Model\Vpayment $vpayment
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\Session $session,
        \Ced\CsMarketplace\Helper\Acl $acl,
        Filesystem $filesystem,
        \Ced\CsMarketplace\Model\Vpayment $vpayment,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->setList();
        $this->session = $session;
        $this->_filesystem = $filesystem;
        $this->_acl = $acl;
        $this->setDirectory();
        $this->vpayment = $vpayment;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * Sets current collection
     *
     */
    public function setList()
    {
        $this->_list = $this->getVPayments();
    }

    /**
     * @return array|\Ced\CsMarketplace\Model\ResourceModel\Vpayment\Collection
     */
    public function getVPayments()
    {
        $payments = [];
        if ($this->session && $this->session->getVendorId()) {
            $vendor = $this->session->getVendor();
            $payments = $vendor->getVendorPayments()->setOrder('created_at', 'DESC');
            $payments = $this->filterPayment($payments);
        }
        return $payments;
    }

    /**
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vpayment\Collection $payment
     * @return mixed
     */
    protected function filterPayment($payment)
    {
        $params = ($this->session) ? $this->session->getData('payment_filter') : [];

        if (is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if ($field == "__SID")
                    continue;

                if (is_array($value)) {
                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        if ($field == 'created_at') {
                            $from = date("Y-m-d 00:00:00", strtotime($from));
                        }

                        $payment->addFieldToFilter($field, array('gteq' => $from));
                    }

                    if (isset($value['to']) && urldecode($value['to']) != "") {
                        $to = urldecode($value['to']);
                        if ($field == 'created_at') {
                            $to = date("Y-m-d 59:59:59", strtotime($to));
                        }

                        $payment->addFieldToFilter($field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    if ($field == 'payment_method') {
                        $payment->addFieldToFilter($field,
                            array("in" => $this->_acl->getDefaultPaymentTypeValue(urldecode($value))));
                    } else {
                        $payment->addFieldToFilter($field, array("like" => '%' . urldecode($value) . '%'));
                    }
                }
            }
        }
        return $payment;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function setDirectory()
    {
        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Generates CSV file with product's list according to the collection in the $this->_list
     * @return array|bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getVendorCommision()
    {
        if ($this->_list !== null) {
            $items = $this->_list->getItems();
            if (count($items) > 0) {
                $name = sha1(microtime());

                $file = $this->_path . '/' . $name . '.csv';
                $this->_directory->create($this->_path);
                $stream = $this->_directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($this->_getCsvHeaders($items));
                $payment_status = $this->vpayment->getStatuses();
                foreach ($items as $payment) {
                    $payment['transaction_type'] =
                        ($payment->getData('transaction_type') == 0) ? __('Credit Type') : __('Debit Type');
                    $payment['payment_method'] =
                        $this->_acl->getDefaultPaymentTypeLabel($payment->getData('payment_method'));
                    $index = $payment['status'];
                    if (isset($index))
                        $payment['status'] = $payment_status[$index]->getText();
                    $stream->writeCsv($payment->getData());
                }
                return [
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true // can delete file after use
                ];
            }
        }
        return false;
    }

    /**
     * Returns indexes of the fetched array as headers for CSV
     *
     * @param $payment
     * @return array
     */
    protected function _getCsvHeaders($payment)
    {
        $_payment = current($payment);
        $headers = array_keys($_payment->getData());
        return $headers;
    }

    /**
     * @param $vendor
     * @return array|bool
     */
    public function _getTransactionsStats($vendor)
    {
        if ($vendor != null && $vendor && $vendor->getId()) {
            $model = $vendor->getAssociatedOrders();
            $model->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns('payment_state')
                ->columns('COUNT(*) as count')
                ->columns('SUM(base_order_total) as order_total')
                ->columns('(SUM(base_order_total) - SUM(shop_commission_base_fee)) AS net_amount')
               ->where("order_payment_state='" . \Magento\Sales\Model\Order\Invoice::STATE_PAID . "'")
                ->group("payment_state");
            return $model && count($model->getData()) ? $model : [];
        }
        return false;
    }

    /**
     * @param $vendor
     * @param $status
     * @return array|bool
     */
    public function _getVendorTransactionsStats($vendor, $status)
    {
        $this->_vendor = $vendor;
        if ($this->_vendor != null && $this->_vendor && $this->_vendor->getId()) {
           
            $model = $this->_vendor->getAssociatedOrders();
            $model->getSelect()
                    ->reset(\Magento\Framework\DB\Select::COLUMNS)
                    ->columns('payment_state')
                    ->columns('COUNT(*) as count')
                    ->columns('SUM(base_order_total) as order_total')
                    ->columns('(SUM(base_order_total) - SUM(shop_commission_base_fee)) AS pending_amount')
                    ->where("payment_state='".$status."'")
                    ->where("order_payment_state='" . \Magento\Sales\Model\Order\Invoice::STATE_PAID . "'")
                    ->group("payment_state");
            
            return $model && count($model->getData()) ? $model : [];
        }
        return false;
    }
}
