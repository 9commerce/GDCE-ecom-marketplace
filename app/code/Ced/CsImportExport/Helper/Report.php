<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;

/**
 * ImportExport history reports helper
 */
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Magento\Framework\Stdlib\DateTime\Timezone */
    protected $timeZone;

    /** @var \Magento\Framework\Filesystem\Directory\WriteInterface */
    protected $varDirectory;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timeZone
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\Timezone $timeZone,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->timeZone = $timeZone;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Calculate import time
     *
     * @param string $time
     * @return string
     */
    public function getExecutionTime($time)
    {
        $reportTime = $this->timeZone->date($time, $this->timeZone->getConfigTimezone());
        $timeDiff = $reportTime->diff($this->timeZone->date());
        return $timeDiff->format('%H:%M:%S');
    }

    /**
     * Get import summary
     *
     * @param \Magento\ImportExport\Model\Import $import
     * @return string
     */
    public function getSummaryStats(\Ced\CsImportExport\Model\Import $import)
    {
        $message = __(
            'Created: %1, Updated: %2, Deleted: %3',
            $import->getCreatedItemsCount(),
            $import->getUpdatedItemsCount(),
            $import->getDeletedItemsCount()
        );
        return $message;
    }

    /**
     * Checks imported file exists.
     *
     * @param string $filename
     * @return bool
     */
    public function importFileExists($filename)
    {
        return $this->varDirectory->isFile($this->getFilePath($filename));
    }

    /**
     * Get report file output
     *
     * @param string $filename
     * @return string
     */
    public function getReportOutput($filename)
    {
        return $this->varDirectory->readFile($this->getFilePath($filename));
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getReportAbsolutePath($fileName)
    {
        return $this->varDirectory->getAbsolutePath(Import::IMPORT_HISTORY_DIR . $fileName);
    }

    /**
     * Retrieve report file size
     *
     * @param string $filename
     * @return int|mixed
     */
    public function getReportSize($filename)
    {
        return $this->varDirectory->stat($this->getFilePath($filename))['size'];
    }

    /**
     * Get file path.
     *
     * @param string $filename
     * @return string
     */
    protected function getFilePath($filename)
    {
        return $this->varDirectory->getRelativePath(Import::IMPORT_HISTORY_DIR . $filename);
    }
}
