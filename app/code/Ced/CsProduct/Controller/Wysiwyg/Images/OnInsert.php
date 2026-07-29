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
 * Class OnInsert
 * @package Ced\CsProduct\Controller\Wysiwyg\Images
 */
class OnInsert extends \Ced\CsProduct\Controller\Wysiwyg\Images
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
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * OnInsert constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $storage
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Ced\CsProduct\Helper\Wysiwyg\Images $images
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsProduct\Model\Wysiwyg\Images\Storage $storage,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Ced\CsProduct\Helper\Wysiwyg\Images $images,
        \Magento\Catalog\Helper\Data $catalogHelper
    )
    {
        parent::__construct($context, $coreRegistry, $storage);

        $this->resultRawFactory = $resultRawFactory;
        $this->images = $images;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Fire when select image
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $imagesHelper = $this->images;
        $request = $this->getRequest();

        $storeId = $request->getParam('store');

        $filename = $request->getParam('filename');
        $filename = $imagesHelper->idDecode($filename);

        $asIs = $request->getParam('as_is');

        $forceStaticPath = $request->getParam('force_static_path');

        $this->catalogHelper->setStoreId($storeId);
        $imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            $image = parse_url($imagesHelper->getCurrentUrl() . $filename, PHP_URL_PATH);
        } else {
            $image = $imagesHelper->getImageHtmlDeclaration($filename, $asIs);
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($image);
    }
}
