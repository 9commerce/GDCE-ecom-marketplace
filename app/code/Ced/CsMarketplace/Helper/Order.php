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
 * Class Order
 * @package Ced\CsMarketplace\Helper
 */
class Order extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $session;

    /**
     * Order constructor.
     * @param \Ced\CsMarketplace\Model\Session $session
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Filesystem $filesystem
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Session $session,
        \Magento\Framework\App\Helper\Context $context,
        Filesystem $filesystem,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
    ) {
        $this->session = $session;
        $this->setList();
        $this->_filesystem = $filesystem;
        $this->setDirectory();
        $this->invoiceFactory = $invoiceFactory;
        $this->vordersFactory = $vordersFactory;
        parent::__construct($context);
    }

    /**
     * Sets current collection
     *
     */
    public function setList()
    {
        $this->_list = $this->getVOrders();
    }

    /**
     * @return array|\Ced\CsMarketplace\Model\ResourceModel\Vorders\Collection
     */
    public function getVOrders()
    {
        $filterCollection = [];
        if ($vendorId = $this->session->getVendorId()) {
            $ordersCollection = $this->session->getVendor()->getAssociatedOrders()->setOrder('id', 'DESC');
            $main_table = 'main_table';
            $order_total = 'order_total';
            $shop_commission_fee = 'shop_commission_fee';

            $ordersCollection->getSelect()->columns([
                'net_vendor_earn' => new \Zend_Db_Expr(
                    "({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})"
                )
            ]);

            $filterCollection = $this->filterOrders($ordersCollection);
        }

        return $filterCollection;
    }

    /**
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vorders\Collection $ordersCollection
     * @return mixed
     */
    protected function filterOrders($ordersCollection)
    {
        $params = $this->session->getData('order_filter');
        $main_table = 'main_table';
        $order_total = 'order_total';
        $shop_commission_fee = 'shop_commission_fee';
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if ($field == '__SID')
                    continue;

                if (is_array($value)) {
                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        if ($field == 'created_at') {
                            $from = date("Y-m-d 00:00:00", strtotime($from));
                        }
                        if ($field == 'net_vendor_earn')
                            $ordersCollection->getSelect()
                                ->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) >='" .
                                    $from . "'");
                        else
                            $ordersCollection->addFieldToFilter($main_table . '.' . $field, array('gteq' => $from));
                    }
                    if (isset($value['to']) && urldecode($value['to']) != "") {
                        $to = urldecode($value['to']);
                        if ($field == 'created_at') {
                            $to = date("Y-m-d 59:59:59", strtotime($to));
                        }
                        if ($field == 'net_vendor_earn')
                            $ordersCollection->getSelect()
                                ->where("({$main_table}.{$order_total}- {$main_table}.{$shop_commission_fee}) <='" .
                                    $to . "'");
                        else
                            $ordersCollection->addFieldToFilter($main_table . '.' . $field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    $ordersCollection->addFieldToFilter($main_table . '.' . $field,
                        array("like" => '%' . urldecode($value) . '%'));
                }
            }
        }
        return $ordersCollection;
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
    public function getCsvData()
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
                $statusArray = $this->invoiceFactory->create()->getStates();
                $paymentarray = $this->vordersFactory->create()->getStates();
                foreach ($items as $payment) {
                    if (isset($payment['order_payment_state']))
                        $payment['order_payment_state'] = isset($statusArray[$payment['order_payment_state']]) ?
                            $statusArray[$payment['order_payment_state']] : "";
                    if (isset($payment['payment_state']))
                        $payment['payment_state'] =
                            isset($paymentarray[$payment['payment_state']]) ? $paymentarray[$payment['payment_state']] :
                                "";
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
}
