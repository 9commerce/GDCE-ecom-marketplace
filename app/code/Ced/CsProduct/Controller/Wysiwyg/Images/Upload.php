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

declare(strict_types=1);

namespace Ced\CsProduct\Controller\Wysiwyg\Images;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\App\Filesystem\DirectoryResolver;

/**
 * Class Upload
 * @package Ced\CsProduct\Controller\Wysiwyg\Images
 */
class Upload extends \Ced\CsProduct\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * Upload constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $storage
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryResolver|null $directoryResolver
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsProduct\Model\Wysiwyg\Images\Storage $storage,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ?DirectoryResolver $directoryResolver = null
    )
    {
        parent::__construct($context, $coreRegistry, $storage);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->directoryResolver = $directoryResolver ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(DirectoryResolver::class);

    }

    /**
     * Files upload processing.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $path = $this->getStorage()->getSession()->getCurrentPath();
            if (!$this->directoryResolver->validatePath($path, DirectoryList::MEDIA)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Directory %1 is not under storage root path.', $path)
                );
            }

            $image = $this->getRequest()->getFiles('image');
            if($image['type'] != '' || $image['name'] != null) {
                $uploaded = $this->getStorage()->uploadFile($path, $image['type']);
            } else {
                $result = ['error' => __('File can not be uploaded.')];
            }

            $response = [
                'name' => $uploaded['name'],
                'type' => $uploaded['type'],
                'error' => $uploaded['error'],
                'size' => $uploaded['size'],
                'file' => $uploaded['file']
            ];
        } catch (\Exception $e) {
            $response = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }
}
