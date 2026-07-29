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

namespace Ced\CsMarketplace\Helper\Tool;

use Magento\Framework\App\Filesystem\DirectoryList;


/**
 * Class Image
 * @package Ced\CsMarketplace\Helper\Tool
 */
class Image extends \Ced\CsMarketplace\Helper\Data
{

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    public $_assetRepo;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var
     */
    protected $_imageFactory;

    /**
     * @var
     */
    protected $_mediaDirectory;

    /**
     * @var
     */
    protected $_vendor;

    /**
     * Image constructor.
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ValueInterface $value
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Catalog\Model\Product\ActionFactory $actionFactory
     * @param \Magento\Indexer\Model\ProcessorFactory $processorFactory
     * @param \Magento\Catalog\Model\Product\Website $website
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Component\ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ValueInterface $value,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory,
        \Magento\Indexer\Model\ProcessorFactory $processorFactory,
        \Magento\Catalog\Model\Product\Website $website,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct(
            $filterManager, $moduleRegistry, $cacheTypeList, $cacheFrontendPool, $request, $productMetadata,
            $storeManager, $value, $transaction, $requestInterface,
            $state, $websiteFactory, $actionFactory, $processorFactory, $website, $vshop, $resourceConnection,
            $deploymentConfig, $notificationFactory,
            $vproductsFactory, $vendorFactory, $stringUtils, $vpaymentFactory, $store, $context
        );
        $this->_assetRepo = $assetRepo;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @param $image
     * @param $attr
     * @param null $width
     * @param null $height
     * @param bool $keepAspectRatio
     * @param bool $keepFrame
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getResizeImage(
        $image,
        $attr,
        $width = null,
        $height = null,
        $keepAspectRatio = true,
        $keepFrame = true
    ) {
        $this->_imageFactory = $this->adapterFactory;
        $this->_mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $absolutePath = $this->_mediaDirectory->getAbsolutePath() . $image;

        if (!$this->_mediaDirectory->isFile($absolutePath)) {
            $imgpath = $this->getStoreConfig(
                'ced_vshops/general/vshoppage_vendor_placeholder',
                $this->_storeManager->getStore(null)->getId()
            );

            if ($attr == "logo" && $imgpath) {
                $image = "ced/csmarketplace/" . $imgpath;
                $absolutePath = $this->_mediaDirectory->getAbsolutePath() . $image;
            } elseif ($attr == "banner" && $this->getStoreConfig('ced_vshops/general/vshoppage_banner_placeholder',
                    $this->_storeManager->getStore(null)->getId())
            ) {
                $imgpath = $this->getStoreConfig(
                    'ced_vshops/general/vshoppage_banner_placeholder',
                    $this->_storeManager->getStore(null)->getId()
                );
                $image = "ced/csmarketplace/" . $imgpath;
                $absolutePath = $this->_mediaDirectory->getAbsolutePath() . $image;
            } else {
                $image = 'Ced_CsMarketplace::images/ced/csmarketplace/vendor/placeholder/' . $attr . '.jpg';
                return $this->getViewFileUrl($image);
            }
        }

        $path = 'catalog/product/cache';
        if ($width !== null) {
            $path .= '/' . $width . 'x';
            if ($height !== null) {
                $path .= $height;
            }
        } else {
            return $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $image;
        }

        $finalPathToWrite = $path . '/' . $attr . '/' . $image;
        $imageResized = $this->_mediaDirectory->getAbsolutePath($finalPathToWrite);

        if (!$this->_mediaDirectory->isFile($finalPathToWrite)) {
            $imageFactory = $this->_imageFactory->create();
            $imageFactory->open($absolutePath);
            $imageFactory->quality(100);
            $imageFactory->constrainOnly(true);
            $imageFactory->keepAspectRatio($keepAspectRatio);
            $imageFactory->keepFrame($keepFrame);
            $imageFactory->keepTransparency(true);
            $imageFactory->backgroundColor([255, 255, 255]);
            $imageFactory->resize($width, $height);
            $imageFactory->save($imageResized);
        }
        return $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $finalPathToWrite;
    }

    /**
     * @param $fileId
     * @return bool|string
     */
    public function getViewFileUrl($fileId)
    {
        try {
            $params = ['_secure' => $this->_request->isSecure()];
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return False;
        }
    }

    /**
     * @param $file
     * @return string
     */
    public function getMediaFileUrl($file)
    {
        return $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'ced/csmarketplace/' . $file;
    }

    /**
     * Get current Vendor
     *
     * @return \Ced\CsMarketplace\Model\Vendor
     */
    protected function getVendor()
    {
        return $this->_vendor;
    }

    /**
     * Set current Vendor
     *
     * @param $vendor
     * @return $this
     */
    protected function setVendor($vendor)
    {
        $this->_vendor = $vendor;
        return $this;
    }
}
