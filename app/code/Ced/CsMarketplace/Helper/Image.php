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

namespace Ced\CsMarketplace\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;


/**
 * Class Image
 * @package Ced\CsMarketplace\Helper
 */
class Image extends Data
{

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @var File
     */
    protected $_file;

    /**
     * Image constructor.
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
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     */
    public function __construct(
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
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    )  {
        parent::__construct(
            $filterManager, $moduleRegistry, $cacheTypeList, $cacheFrontendPool, $request, $productMetadata,
            $storeManager, $value, $transaction, $requestInterface,
            $state, $websiteFactory, $actionFactory, $processorFactory, $website, $vshop, $resourceConnection,
            $deploymentConfig, $notificationFactory,
            $vproductsFactory, $vendorFactory, $stringUtils, $vpaymentFactory, $store, $context
        );
        $this->_file = $driverFile;
        $this->request = $requestInterface;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @param bool $attributes
     * @return array
     * @throws LocalizedException
     */
    public function UploadImage($attributes = false)
    {
        if (!$attributes) {
            $imagefields = ['profile_picture', 'company_logo', 'company_banner'];
            foreach ($imagefields as $fieldName) {
                $this->UploadImagebyName($fieldName);
            }
        } else {
            foreach ($attributes as $attribute) {
                if ($attribute->getFrontendInput() == 'file') {
                    $this->UploadFilebyName($attribute->getAttributeCode());
                } else {
                    $this->UploadImagebyName($attribute->getAttributeCode());
                }
            }
        }
        return $this->data;
    }

    /**
     * @param $fieldName
     * @throws LocalizedException
     */
    public function UploadImagebyName($fieldName)
    {

        $vendorPost = $this->request->getParam('vendor');
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vendor');
        $allowed_type = ['jpg', 'jpeg', 'gif', 'png'];
        if ($this->_moduleManager->isEnabled('Ced_CsVendorAttribute')) {
            $allowed_type = explode(',',
                $this->_scopeConfigManager->getValue(
                    'ced_csvendorattribute/vendorattribute/allowed_image_type',
                    ScopeInterface::SCOPE_STORE, $this->getStore()->getId()
                )
            );
        }

        try {

            $Files = $this->request->getFiles()->toArray();

            $file = [];
            if (isset($Files['vendor'])) {
                $file = $Files['vendor'][$fieldName];
            }

            $uploader = $this->uploaderFactory->create(array('fileId' => "vendor[{$fieldName}]"));
            $uploader->setAllowedExtensions($allowed_type); // or pdf or anything
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileData = $uploader->validateFile();
            $extension = $uploader->getFileExtension();
            $fileName = $fieldName.time().'.'.$extension;
            $flag = $uploader->save($path, $fileName);
            $imagePath = true;
            $this->data[$fieldName] = 'ced/csmaketplace/vendor/'.$fileName;
        } catch(\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        if (empty($imagePath)) {
            if (isset($vendorPost[$fieldName]['delete']) && $vendorPost[$fieldName]['delete'] == 1) {
                $this->data[$fieldName] = '';
                $imageName = explode('/', $vendorPost[$fieldName]['value']);
                $imageName = $imageName[count($imageName)-1];
                $this->_file->deleteFile($path.'/'.$imageName);
            } else {
                unset($this->data[$fieldName]);
            }
        }
    }

    /**
     * @param $fieldName
     * @throws LocalizedException
     */
    public function UploadFilebyName($fieldName)
    {
        $vendorPost = $this->request->getParam('vendor');
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('ced/csmaketplace/vendor');
        $allowed_type = ['jpg','jpeg','gif','png','pdf','doc','docx'];
        if($this->_moduleManager->isEnabled('Ced_CsVendorAttribute')){
            $allowed_type = explode(',',$this->_scopeConfigManager->getValue(
                'ced_csvendorattribute/vendorattribute/allowed_file_type',
                ScopeInterface::SCOPE_STORE,$this->getStore()->getId())
            );
        }
        $imagePath = false;
        try {
            $uploadFactory = $this->uploaderFactory;
            $uploader = $uploadFactory->create(array('fileId' => "vendor[{$fieldName}]"));
            $uploader->setAllowedExtensions($allowed_type);
            $fileData = $uploader->validateFile();
            $uploader->setAllowRenameFiles(false);
            $extension = $uploader->getFileExtension();;
            $uploader->setFilesDispersion(false);
            $fileName = $fieldName.time().'.'.$extension;
            $flag = $uploader->save($path, $fileName);
            $this->data[$fieldName] = 'ced/csmaketplace/vendor/'.$fileName;
            $imagePath = true;
        } catch(\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        if ( !$imagePath) {
            if (isset($vendorPost[$fieldName]['delete']) && $vendorPost[$fieldName]['delete'] == 1) {
                $this->data[$fieldName] = '';
                $imageName = explode('/', $vendorPost[$fieldName]['value']);
                $imageName = $imageName[count($imageName)-1];
                $this->_file->deleteFile($path.'/'.$imageName);
            } else {
                unset($this->data[$fieldName]);
            }
        }
    }

    /**
     * @param $file
     */
    public function deleteVendorImage($file)
    {
        if ($this->_file->isExists($file))  {
            $this->_file->deleteFile($file);
        }
    }
}
