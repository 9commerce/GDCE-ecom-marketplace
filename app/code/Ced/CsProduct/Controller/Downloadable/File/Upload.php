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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Downloadable\File;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\ResultFactory;

class Upload extends \Ced\CsProduct\Controller\Vproducts
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_sample;

    /**
     * @var \Ced\CsProduct\Helper\File
     */
    protected $_fileHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $storageDatabase;

    /**
     * @param \Magento\Downloadable\Model\Link $modelLink
     * @param \Magento\Downloadable\Model\Sample $sample
     * @param \Ced\CsProduct\Helper\File $fileHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
     * @param \Magento\Framework\App\Request\Http $http
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     */
    public function __construct(
        \Magento\Downloadable\Model\Link $modelLink,
        \Magento\Downloadable\Model\Sample $sample,
        \Ced\CsProduct\Helper\File $fileHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase,
        \Magento\Framework\App\Request\Http $http,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
    ) {
        $this->storageDatabase = $storageDatabase;
        $this->_scopeConfig = $scopeConfig;
        $this->_link = $modelLink;
        $this->_sample = $sample;
        $this->_fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct(
            $scopeConfig,
            $http,
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor,
            $storeManager,
            $productFactory,
            $vproductsFactory,
            $type
        );
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = $this->_sample->getBaseTmpPath();
            $extformat = $this->_scopeConfig->getValue(
                'ced_vproducts/downloadable_config/sample_formats',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } elseif ($type == 'links') {
            $tmpPath = $this->_link->getBaseTmpPath();
            $extformat = $this->_scopeConfig->getValue(
                'ced_vproducts/downloadable_config/link_formats',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } elseif ($type == 'link_samples') {
            $tmpPath = $this->_link->getBaseSampleTmpPath();
            $extformat = $this->_scopeConfig->getValue(
                'ced_vproducts/downloadable_config/link_formats',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        $extformat = explode(',', $extformat);
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $type]);

            $result = $this->_fileHelper->uploadFromTmp($tmpPath, $uploader, $extformat);

            if (!$result) {
                throw new \LocalizedException('File can not be moved from temporary folder to the destination folder.');
            }

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);

            if (isset($result['path'])) {
                $result['path'] = str_replace('\\', '/', $result['path']);
            }
            if (isset($result['file'])) {
                $relativePath = rtrim($tmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->storageDatabase->saveFile($relativePath);
            }

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\RuntimeException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
