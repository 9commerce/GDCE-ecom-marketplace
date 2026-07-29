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


use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Downloadable\Api\Data\SampleInterfaceFactory;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory;

/**
 * Class Link
 * @package Ced\CsMarketplace\Helper\Vproducts
 */
class Link extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Downloadable\Model\SampleFactory
     */
    protected $sampleFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var SampleRepository
     */
    protected $sampleRepository;


    /**
     * @var SampleInterface
     */
    protected $sampleInterfaceFactory;

    /**
     * @var SampleRepository
     */
    protected $linkRepository;


    /**
     * @var SampleInterface
     */
    protected $linkInterfaceFactory;

    /**
     * Link constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Downloadable\Model\SampleFactory $sampleFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Downloadable\Model\SampleFactory $sampleFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        SampleRepository $sampleRepository,
        SampleInterfaceFactory $sampleInterfaceFactory,
        LinkRepository $linkRepository,
        LinkInterfaceFactory $linkInterfaceFactory
    ) {
        $this->filesystem = $filesystem;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->sampleFactory = $sampleFactory;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->linkFactory = $linkFactory;
        $this->productFactory = $productFactory;
        $this->sampleRepository = $sampleRepository;
        $this->sampleInterfaceFactory = $sampleInterfaceFactory;
        $this->linkRepository = $linkRepository;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        parent::__construct($context);
    }

    /**
     *  Upload Downloadable product data
     * @param $type
     * @param $data
     * @return array
     * @throws LocalizedException
     */
    public function uploadDownloadableFiles($type, $data)
    {
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $uploadDir = $mediaDirectory->getAbsolutePath("downloadable/files/" . $type . "/");

        switch($type) {
            case "samples":
            case "link_samples":
                $formats = $this->csmarketplaceHelper->getStoreConfig(
                    'ced_vproducts/downloadable_config/sample_formats'
                );
                break;

            case "links":
                $formats = $this->csmarketplaceHelper->getStoreConfig(
                    'ced_vproducts/downloadable_config/link_formats'
                );
                break;

            default:
                $formats = '';
                break;
        }

        $tempArr = explode(',', $formats);

        $formats_array = [];
        foreach ($tempArr as $value) {
            if (strlen($value)) {
                $formats_array [] = trim($value);
            }
        }

        $uploaded_files_array = [];
        $uploader = '';
        if (isset($data) && count($data) > 0) {
            foreach ($data as $key => $value) {
                if ($type == "link_samples") {
                    if (isset($data [$key] ['sample']['type']) && $data [$key] ['sample']['type'] == "url") {
                        continue;
                    }
                } else {
                    if (isset($data [$key] ['type']) && $data [$key] ['type'] == "url") {
                        continue;
                    }
                }
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => "{$type}[{$key}]"]);
                } catch (\Exception $e) {
                    $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                }
                if ($uploader) {
                    $file_data = $uploader->validateFile();
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $uploader->setAllowedExtensions($formats_array);
                    $file = $file_data ['name'];
                    try {
                        if ($result = $uploader->save($uploadDir, $file)) {
                            $uploaded_files_array [$key] = $result ['file'];
                        }
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                    }
                }
            }
        }
        return $uploaded_files_array;
    }

    /**
     * Helper for saving downloadable product Links data
     *
     * @param $samplesdata
     * @param $samples
     * @param $product
     */

    public function processSamplesData($samplesdata, $samples, $product){

        if (is_array($samplesdata) && count($samplesdata) > 0) {
            $currentStore = $this->storeManager->getStore()->getId();
            foreach ($samplesdata as $key => $val) {
                $sampleInterface =   $this->sampleInterfaceFactory->create();
                $sampleModel = $this->sampleFactory->create();
                if ($samplesdata[$key]['sample_id'] != '') {
                    $sampleInterface->setId($samplesdata[$key]['sample_id']);
                    $sampleModel->load($samplesdata[$key]['sample_id']);
                    if($sampleModel && $sampleModel->getId()){
                        $sampleInterface->setSampleFile($sampleModel->getSampleFile());
                        $sampleInterface->setSampleType($sampleModel->getSampleType());
                    }

                }

                $sampleInterface->setTitle(isset($samplesdata[$key]['title']) ? $samplesdata[$key]['title'] : '');
                $sampleInterface->setSortOrder(isset($samplesdata[$key]['sort_order']) ? (int)$samplesdata[$key]['sort_order'] :
                    0);

                if (isset($samplesdata[$key]['type']) && ($samplesdata[$key]['type'] == 'file') && isset($samples[$key])) {
                    $sampleInterface->setSampleFile("/" . $samples [$key]);
                    $sampleInterface->setSampleType("file");
                } else if (isset($samplesdata[$key]['type']) && ($samplesdata[$key]['type'] == 'url') && isset($samplesdata[$key]['sample_url'])) {
                    $sampleInterface->setSampleType("url");
                    $sampleInterface->setSampleUrl($samplesdata[$key]['sample_url']);
                    }

                try{
                    $this->sampleRepository->save($product->getSku(), $sampleInterface);
                }catch(\Exception $e){
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Something went wrong while saving the file(s). Details: %1', $e->getMessage())
                    );

                    }
                }
            }

        return $this;

        }


    /**
     * Helper for saving downloadable product Links data
     *
     * @params array $data,array $samples
     * @param $linksdata
     * @param $links
     * @param $link_samples
     * @param $product
     */
    public function processLinksData($linksdata, $links, $link_samples, $product){

        if (is_array($linksdata) && count($linksdata) > 0) {
            $currentStore = $this->storeManager->getStore()->getId();

            foreach ($linksdata as $key => $val) {
                $linkInterface =   $this->linkInterfaceFactory->create();
                $linkModel = $this->linkFactory->create();

                if (isset($linksdata[$key]['link_id']) && $linksdata[$key]['link_id'] != '') {
                    $linkModel = $this->linkFactory->create()->load($linksdata[$key]['link_id']);
                    if($linkModel && $linkModel->getId()){
                        $linkInterface->setId($linkModel->getId());
                        $linkInterface->setLinkType($linkModel->getLinkType());
                        $linkInterface->setLinkFile($linkModel->getLinkFile());
                        $linkInterface->setSampleFile($linkModel->getSampleFile());
                    }

                } else if (isset($linksdata[$key]['link_id']) && $linksdata[$key]['link_id'] == '') {
                    $linkInterface->setProductId($product->getId());
                    $linkInterface->setStoreId($currentStore);
                    $linkInterface->setWebsiteId(0);
                    $linkInterface->setProductWebsiteIds($product
                        ->getWebsiteIds());
                }


                $linkInterface->setPrice(isset($linksdata[$key]['price']) ? $linksdata[$key]['price'] : 0);
                $linkInterface->setSortOrder(isset($linksdata[$key]['sort_order']) ? (int)$linksdata[$key]['sort_order'] : 0);
                $linkInterface->setTitle(isset($linksdata[$key]['title']) ? $linksdata[$key]['title'] : '');
                if (isset($linksdata[$key]['is_unlimited'])) {
                    if ($linksdata[$key]['is_unlimited'] == 1) {
                        $linkInterface->setNumberOfDownloads(0);
                    }
                } else {
                    $linkInterface->setNumberOfDownloads(isset($linksdata[$key]['number_of_downloads']) ?
                        $linksdata[$key]['number_of_downloads'] : 0);
                }

                if (isset($linksdata[$key]['type']) && $linksdata[$key]['type'] == 'file' && isset($links[$key])) {
                    $linkInterface->setLinkFile("/" . $links[$key]);
                    $linkInterface->setLinkType("file");
                } else if (isset($linksdata[$key]['type']) && ($linksdata[$key]['type'] == 'url') &&
                    isset($linksdata[$key]['link_url'])
                ) {
                    $linkInterface->setLinkType("url");
                    $linkInterface->setLinkUrl($linksdata[$key]['link_url']);
                }

                if (isset($linksdata[$key]['sample']['type']) && ($linksdata[$key]['sample']['type'] == 'file') &&
                    isset($link_samples[$key])
                ) {
                    $linkInterface->setSampleFile("/" . $link_samples[$key]);
                    $linkInterface->setSampleType("file");
                } else if (isset($linksdata[$key]['sample']['type']) && ($linksdata[$key]['sample']['type'] == 'url') &&
                    isset($linksdata[$key] ['sample']['sample_url'])
                ) {
                    $linkInterface->setSampleType("url");
                    $linkInterface->setSampleUrl($linksdata[$key]['sample']['sample_url']);
                }

                try{
                    $this->linkRepository->save($product->getSku(), $linkInterface);
                }catch(\Exception $e){
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Something went wrong while saving the file(s). Details: %1', $e->getMessage())
                    );

                }
            }
        }

        return $this;

}


}
