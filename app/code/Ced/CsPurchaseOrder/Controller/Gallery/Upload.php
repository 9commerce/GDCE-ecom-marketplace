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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Gallery;

/**
 * Class Upload
 * @package Ced\CsPurchaseOrder\Controller\Gallery
 */
class Upload extends \Magento\Framework\App\Action\Action
{

    /**
     * @var array
     */
    public $data = array();

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $custmoersession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $fileSystem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Upload constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
    )
    {
        $this->custmoersession = $session;
        $this->_storeManager = $storeManager;
        $this->fileSystem = $fileSystem;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($context);

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $mediaDirectory = $this->fileSystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('cspurchaseorder/images/' . $this->custmoersession
                ->getCustomerId());
        $imagePath = false;
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/images/' . $this
                ->custmoersession->getCustomerId() . '/';
        try {
            $uploader = $this->uploaderFactory->create(array('fileId' => "file"));
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileData = $uploader->validateFile();
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $fileName = $fileData['name'] . time() . '.' . $extension;
            $flag = $uploader->save($path, $fileName);
            $imagePath = true;
            $imagecontent = array("name" => $fileName, "size" => $fileData['size'], "src" => $url . $fileName,
                "imagepath" => $path . '/' . $fileName);

            $result = $this->resultJsonFactory->create();
            return $result->setData($imagecontent);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }
}