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

namespace Ced\CsMarketplace\Helper\Vreports;

use Ced\CsMarketplace\Helper\Report;
use Ced\CsMarketplace\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;


/**
 * Class Vorders
 * @package Ced\CsMarketplace\Helper\Vreports
 */
class Vorders extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var null
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
     * @var Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var
     */
    protected $_filtercollection;

    /**
     * Vorders constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     * @param Session $customerSession
     * @param Report $reportHelper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Session $customerSession,
        Report $reportHelper
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->customerSession = $customerSession;
        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->reportHelper = $reportHelper;

    }

    /**
     * Generates CSV file with product's list according to the collection in the $this->_list
     * @return array|bool
     */
    public function getCsvData()
    {
        $this->setList();

        if (($this->_list !== null) && is_object($this->_list)) {
            $items = $this->_list->getItems();

            if (count($items) > 0) {
                $name = sha1(microtime());
                $file = $this->_path . '/' . $name . '.csv';
                $this->_directory->create($this->_path);
                $stream = $this->_directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($this->_getCsvHeaders($items));

                foreach ($items as $payment) {
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
     * Sets current collection
     *
     */
    public function setList()
    {
        if (empty($this->_list))
            $this->_list = $this->getVordersReports();
    }

    /**
     * @return array|bool
     */
    public function getVordersReports()
    {
        $params = ($this->customerSession) ? $this->customerSession->getData('vorders_reports_filter') : null;
        $ordersCollection = null;

        if (isset($params) && $params != null) {
            $ordersCollection =
                $this->reportHelper->getVordersReportModel($this->customerSession->getVendor(), $params['period'],
                    $params['from'], $params['to'], $params['payment_state']);

            if (count($ordersCollection) > 0) {
                return $ordersCollection;
            }
        }
        return $ordersCollection;
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
