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
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;

use Magento\Backend\App\Action;

/**
 * Class Upload
 * @package Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class Upload extends Action
{

    /**
     * @var \Ced\CsEnhancement\Helper\Uploader
     */
    protected $uploaderHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonEncoder;

    /**
     * Upload constructor.
     * @param \Ced\CsEnhancement\Helper\Uploader $uploaderHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     * @param Action\Context $context
     */
    public function __construct(
        \Ced\CsEnhancement\Helper\Uploader $uploaderHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->uploaderHelper = $uploaderHelper;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        $return = ['success' => false];

        if (!empty($action)) {
            switch ($action) {
                case 'upload':
                    $return = $this->uploadCsv();
                    break;

                case 'delete':
                    $return = $this->deleteFile();
                    break;
            }
        }

        $this->getResponse()->setBody($this->jsonEncoder->serialize($return));
    }

    /**
     * @return array|bool
     */
    public function uploadCsv()
    {
        $uploadResult = false;
        $file = $this->getRequest()->getFiles();
        foreach ($file as $fileId) {
            $uploadResult = $this->uploaderHelper->csvUploader($fileId);
        }
        return $uploadResult;
    }

    /**
     * @return bool
     */
    protected function deleteFile()
    {
        $path = $this->getRequest()->getParam('path');
        $result = $this->uploaderHelper->deleteFile($path);
        return $result;
    }
}
