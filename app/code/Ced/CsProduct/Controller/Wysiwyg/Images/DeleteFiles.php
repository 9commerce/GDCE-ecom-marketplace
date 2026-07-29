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
 * @package     Ced_CsProduct
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class DeleteFiles
 * @package Ced\CsProduct\Controller\Wysiwyg\Images
 */
class DeleteFiles extends \Ced\CsProduct\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @var \Ced\CsProduct\Helper\Wysiwyg\Images
     */
    protected $images;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * DeleteFiles constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $storage
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver
     * @param \Ced\CsProduct\Helper\Wysiwyg\Images $images
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsProduct\Model\Wysiwyg\Images\Storage $storage,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Filesystem\DirectoryResolver $directoryResolver,
        \Ced\CsProduct\Helper\Wysiwyg\Images $images,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context, $coreRegistry, $storage);

        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryResolver = $directoryResolver;
        $this->images = $images;
        $this->filesystem = $filesystem;
    }

    /**
     * Delete file from media storage.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $files = $this->getRequest()->getParam('files');

            /** @var $helper \Ced\CsProduct\Helper\Wysiwyg\Images */
            $helper = $this->images;

            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->directoryResolver->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }

            foreach ($files as $file) {
                $file = $helper->idDecode($file);

                $dir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $filePath = $path . '/' . \Magento\Framework\File\Uploader::getCorrectFileName($file);
                if ($dir->isFile($dir->getRelativePath($filePath)) && !preg_match('#.htaccess#', $file)) {
                    $this->getStorage()->deleteFile($filePath);
                }
            }

            return $this->resultRawFactory->create();
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData($result);
        }
    }
}
