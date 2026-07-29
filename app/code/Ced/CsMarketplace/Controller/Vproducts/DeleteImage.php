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

namespace Ced\CsMarketplace\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class DeleteImage
 * @package Ced\CsMarketplace\Controller\Vproducts
 */
class DeleteImage extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\EntryResolver
     */
    public $entryResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\GalleryManagement
     */
    public $galleryManagement;

    /**
     * DeleteImage constructor.
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
     * @param \Magento\Catalog\Model\Product\Gallery\EntryResolver $entryResolver
     * @param \Magento\Catalog\Model\Product\Gallery\GalleryManagement $galleryManagement
     */
    public function __construct(
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
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Catalog\Model\Product\Gallery\EntryResolver $entryResolver,
        \Magento\Catalog\Model\Product\Gallery\GalleryManagement $galleryManagement
    )
    {
        $this->resultJsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        $this->entryResolver = $entryResolver;
        $this->productFactory = $productFactory;
        $this->galleryManagement = $galleryManagement;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor, $storeManager, $productFactory, $vproductsFactory, $type);
    }
    
    /**
     * @return bool|\Ced\CsMarketplace\Controller\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $data = $this->getRequest()->getParams();

        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        try {

            $entryResolver = $this->entryResolver;

            $product = $this->productFactory->create()->setStoreId($data['storeid'])->load($data['productid']);
            $entryId = $entryResolver->getEntryIdByFilePath($product, $data['imagename']);

            $this->galleryManagement->remove($product->getSku(), $entryId);
            $result = 1;

        } catch (\Exception $e) {
            $result = 0;
        }
        $resultJson->setData($result);
        return $resultJson;
    }
}
