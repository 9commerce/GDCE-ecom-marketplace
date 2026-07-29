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
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Controller\Import;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Uploadimage
 * @package Ced\CsImportExport\Controller\Import
 */
class Uploadimage extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Uploadimage constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultjson
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultjson,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        $this->resultJsonFactory = $resultjson;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Image Uploading
     *
     * @return null
     */
    public function execute()
    {
        $vendorId = $this->session->getVendorId();
        $path = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $path->getAbsolutePath('import/' . $vendorId);

        $response = [];
        $errorCount = 0;
        $successCount = 0;
        if ($vendorId) {
            $_files = $this->getRequest()->getFiles();
            if (!empty($_files['file_upload'])) {
                foreach ($_files['file_upload'] as $key => $image) {
                    if (!empty($image)) {
                        try {
                            $uploader = $this->uploaderFactory->create(['fileId' => "file_upload[{$key}]",]);
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(true);
                            $fileData = $uploader->validateFile();
                            $fileName = time() . $image['name'];
                            $uploader->save($path, $fileName);
                            $successCount++;
                        } catch (\Exception $e) {
                            $errorCount++;
                        }
                    }
                }
            }
        }
        $response['message'] = __('%1 images uploaded successfully %2 failed to upload', $successCount, $errorCount);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
