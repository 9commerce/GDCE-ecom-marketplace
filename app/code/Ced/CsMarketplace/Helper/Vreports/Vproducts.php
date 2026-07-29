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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;


/**
 * Class Vproducts
 * @package Ced\CsMarketplace\Helper\Vreports
 */
class Vproducts extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var \Ced\CsMarketplace\Helper\Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Vproducts constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Filesystem $filesystem
     * @param \Ced\CsMarketplace\Helper\Report $reportHelper
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Filesystem $filesystem,
        \Ced\CsMarketplace\Helper\Report $reportHelper,
        \Ced\CsMarketplace\Model\Session $customerSession
    ) {
        $this->reportHelper = $reportHelper;
        $this->customerSession = $customerSession;
        $this->_filesystem = $filesystem;
        $this->_directory = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Sets current collection
     *
     */
    public function setList()
    {
        if (empty($this->_list))
            $this->_list = $this->getVproductsReports();
    }

    /**
     * @return bool|mixed
     */
    public function getVproductsReports()
    {
        $params = ($this->customerSession) ? $this->customerSession->getData('vproducts_reports_filter') : [];
        $productsCollection = null;

        if (isset($params['from']) && isset($params['to']) && $params != null) {
            $productsCollection =
                $this->reportHelper->getVproductsReportModel(
                    $this->customerSession->getVendor()->getId(),
                    $params['from'],
                    $params['to']
                );
            if (count($productsCollection) > 0) {
                return $productsCollection;
            }
        }
        return $productsCollection;
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
     * Generates CSV file with product's list according to the collection in the $this->_list
     * @return array|bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getCsvData()
    {
        $this->setList();
        if ($this->_list && is_object($this->_list)) {
            $items = $this->_list->getItems();
            if (count($items) > 0) {
                $name = "reports_".time().rand();
                $file = $this->_path . '/' . $name . '.csv';
                $this->_directory->create($this->_path);
                $stream = $this->_directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($this->_getCsvHeaders($items));

                foreach ($items as $payment) {
                    $stream->writeCsv($payment->getData());
                }
                return [
                    'type'  => 'filename',
                    'value' => $file,
                    'rm'    => true // can delete file after use
                ];
            }
        }
        return false;
    }
}
