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

namespace Ced\CsMarketplace\Model\System\Config\Backend\Vshops;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Banner
 * @package Ced\CsMarketplace\Model\System\Config\Backend\Vshops
 */
class Banner extends \Magento\Config\Model\Config\Backend\File
{

    /**
     * Save uploaded file before saving config value
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $filevalue = $this->getValue();
        $tmpName = $this->_requestData->getTmpName($this->getPath());
        $file = [];
        if ($tmpName) {
            $file['tmp_name'] = $tmpName;
            $file['name'] = $this->_requestData->getName($this->getPath());
        } elseif (!empty($filevalue['tmp_name'])) {
            $file['tmp_name'] = $filevalue['tmp_name'];
            $file['name'] = $filevalue['name'];
        }

        if (isset($file['tmp_name'])) {

            /*
             * @note: to check if image is in correct ratio or not
             * */
            $imageData = getimagesizefromstring($file['tmp_name']);
            if (is_array($imageData) && count($imageData) > 0) {
                $width = $imageData[0];
                $height = $imageData[1];
                $ratio = $width / $height;
                $correctImage = $width > $height;
                $minimage = $width >= 1000 && $height >= 300;
                //$allowedRatio = 1000 / 300;

                $validate = ($ratio >= 3.16 && $ratio <= 3.5);

                if (!$correctImage || !$minimage || !$validate) {
                    $this->setValue('');
                    $msg = __("Minimum allowed banner dimension is 1000px X 300px and width to height ratio
                     must be around 10:3.");
                    throw new LocalizedException($msg);
                }
            }

            /* @note: update image */
            $uploadDir = $this->_getUploadDir();
            try {
                $csUploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $csUploader->setAllowRenameFiles(true);
                $csUploader->setAllowedExtensions($this->_getAllowedExtensions());
                $csUploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $csUploader->save($uploadDir);

            } catch (\Exception $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
            if ($result['file']) {
                if ($this->_addWhetherScopeInfo()) {
                    $result['file'] = $this->_prependScopeInfo($result['file']);
                }
                $this->setValue($result['file']);
            }
        } else {
            if (is_array($filevalue) && !empty($filevalue['delete'])) {
                $this->setValue('');
            } else {
                $this->unsValue();
            }
        }
        return $this;
    }

    /**
     * Getter for allowed extensions of uploaded files
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpeg', 'gif', 'jpg', 'png'];
    }
}
