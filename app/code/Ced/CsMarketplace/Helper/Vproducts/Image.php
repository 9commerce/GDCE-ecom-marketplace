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

namespace Ced\CsMarketplace\Helper\Vproducts;

use Magento\Framework\App\Helper\Context;


/**
 * Class Image
 * @package Ced\CsMarketplace\Helper\Vproducts
 */
class Image extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\MediaStorage\Model\File\uploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $action;

    /**
     * Image constructor.
     * @param Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Action $action
     */
    public function __construct(
        Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Action $action
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->action = $action;
        parent::__construct($context);
    }

    /**
     * Save images to media gallery and set product default image
     * @param $product
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveImages($product, $data)
    {
        $defaultimage = '';
        $productid = $product->getId();
        $productModel = clone $product;
        if ($productModel && $productModel->getId()) {
            if (isset($data['real_img_val']) && count($data['real_img_val']) > 0) {
                $Files = $this->_request->getFiles()->toArray();
                foreach ($data['real_img_val'] as $key => $value) {
                    $file = [];
                    if (isset($Files['images'])) {
                        $file = $Files['images'][$key];

                    }
                    $uploader = $this->uploaderFactory->create(['fileId' => $file]);
                    $file_data = $uploader->validateFile();
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $mediaDirectory = $this->filesystem
                        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

                    $targetDir = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vproducts/' . $productid);
                    $image = sha1($file_data['tmp_name']) . $file_data['name'];
                    try {
                        if ($result = $uploader->save($targetDir, $image)) {
                            $fetchTarget = $targetDir . '/' . $result['file'];
                            $productModel->addImageToMediaGallery(
                                $fetchTarget, array(
                                'image',
                                'small_image',
                                'thumbnail'
                            ), false, false
                            );
                            if (isset($data ['defaultimage']) && $data ['defaultimage'] != '') {
                                if ($data ['defaultimage'] == "real_img_val[{$key}]") {
                                    $defaultimage = $result['file'];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                    }
                }
            }
            $productModel->setStoreId(0)->save();

            if (isset($data ['defaultimage']) && $data ['defaultimage'] != '') {
                if ($defaultimage == '') {
                    $defaultimage = $data ['defaultimage'];
                }
                if ($defaultimage !== '') {
                    $mediaGallery = $productModel->getMediaGallery();
                    if (isset($mediaGallery['images'])) {
                        foreach ($mediaGallery['images'] as $image) {
                            if (strpos($image['file'], $defaultimage) !== false) {
                                $this->action
                                    ->updateAttributes([$productid],
                                        ['image' => $image['file'], 'small_image' => $image['file'],
                                            'thumbnail' => $image['file']], 0);
                                break;
                            }
                        }
                    }
                }
            } else {
                $this->action
                    ->updateAttributes([$productid],
                        ['image' => '', 'small_image' => '', 'thumbnail' => ''], 0);
            }
        }
    }
}
