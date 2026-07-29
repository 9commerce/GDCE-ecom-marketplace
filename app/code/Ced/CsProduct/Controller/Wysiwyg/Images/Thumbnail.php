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

/**
 * Class Thumbnail
 * @package Ced\CsProduct\Controller\Wysiwyg\Images
 */
class Thumbnail extends \Ced\CsProduct\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Ced\CsProduct\Helper\Wysiwyg\Images
     */
    protected $images;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * Thumbnail constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $storage
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Ced\CsProduct\Helper\Wysiwyg\Images $images
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsProduct\Model\Wysiwyg\Images\Storage $storage,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Ced\CsProduct\Helper\Wysiwyg\Images $images,
        \Magento\Framework\Image\AdapterFactory $adapterFactory
    )
    {
        parent::__construct($context, $coreRegistry, $storage);

        $this->resultRawFactory = $resultRawFactory;
        $this->images = $images;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Generate image thumbnail on the fly
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $file = $this->images->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        if ($thumb !== false) {

            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            $image = $this->adapterFactory->create();
            $image->open($thumb);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            return $resultRaw;
        } else {
            // todo: generate some placeholder
        }
    }
}
