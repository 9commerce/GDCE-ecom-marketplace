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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AllowedVideo
 * @package Ced\CsMarketplace\Model\Config\Backend
 */
class AllowedVideo extends \Magento\Config\Model\Config\Backend\File
{

    /**
     * Save uploaded file before saving config value
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $file = $this->getFileData();
        $cedValue = $this->getValue();
        if (!empty($file)) {
            $cedUploadDir = $this->_getUploadDir();
            try {
                /** @var Uploader $cedUploader */
                $cedUploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $cedUploader->setAllowedExtensions($this->_getAllowedExtensions());
                $cedUploader->setAllowRenameFiles(true);
                $cedUploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $cedUploader->save($cedUploadDir);
            } catch (\Exception $e) {
                throw new LocalizedException(__('%1', $e->getMessage()));
            }

            $cedFilename = $result['file'];
            if ($cedFilename) {
                if ($this->_addWhetherScopeInfo()) {
                    $cedFilename = $this->_prependScopeInfo($cedFilename);
                }
                $this->setValue($cedFilename);
            }
        } else {
            if (is_array($cedValue) && !empty($cedValue['delete'])) {
                $this->setValue('');
            } elseif (is_array($cedValue) && !empty($cedValue['value'])) {
                $this->setValue($cedValue['value']);
            } else {
                $this->unsValue();
            }
        }

        return $this;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['mp4', 'webm', 'mov'];
    }
}
