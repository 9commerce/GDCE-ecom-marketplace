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
 * @package     Ced_QuickOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\QuickOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DirectoryList as Directory;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Magento\Framework\Component\ComponentRegistrar;
use \Magento\Framework\Filesystem\Directory\ReadFactory;
use \Magento\Framework\Controller\Result\RawFactory;


class Download extends \Magento\Framework\App\Action\Action
{
    const SAMPLE_FILES_MODULE = 'Ced_QuickOrder';
     /**
     * Download constructor.
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param Directory $directory
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        Directory $directory,
        FileFactory $fileFactory,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory,
        RawFactory $resultRawFactory
    ){
        $this->directoryList = $directoryList;
        $this->directory = $directory;
        $this->fileFactory = $fileFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent:: __construct($context);
    }

    /**
     * @return mixed
     */
     public function execute()
        {
            $fileName = 'uploadquick.csv';
            $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::SAMPLE_FILES_MODULE);
            $fileAbsolutePath = $moduleDir . '/SampleCsv/' . $fileName;
            $directoryRead = $this->readFactory->create($moduleDir);
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);
            if (!$directoryRead->isFile($filePath)) {
                $this->messageManager->addError(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('quickorder/index/index');
                return $resultRedirect;
            }
            $fileSize = isset($directoryRead->stat($filePath)['size'])?$directoryRead->stat($filePath)['size'] : null;
            $this->fileFactory->create(
                $fileName,
                null,
                DirectoryList::VAR_DIR,
                'application/octet-stream',
                $fileSize
            );
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($directoryRead->readFile($filePath));
            return $resultRaw;
        }

}


