<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Edit extends \Ced\CsMarketplace\Controller\Vproducts\Edit
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsProduct\Controller\Product\Builder
     */
    protected $productBuilder;

    /**
     * @var UrlFactory
     */
    protected $urlBuilder;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsProduct\Helper\Data
     */
    protected $csproductHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * Edit constructor.
     * @param \Ced\CsProduct\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsProduct\Helper\Data $csproductHelper
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
        \Ced\CsProduct\Controller\Product\Builder $productBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsProduct\Helper\Data $csproductHelper,
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
        $this->productBuilder = $productBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlFactory;
        $this->registry = $registry;
        $this->csproductHelper = $csproductHelper;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct(
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
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->csproductHelper->isVendorLoggedIn()) {
            $this->_redirect('csmarketplace/account/login');
        }
        if (!$this->_scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
            && $this->getRequest()->getModuleName() == 'csmarketplace') {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('csmarketplace_vproducts_' . $this->getRequest()->getParam('type'));
            return $resultPage;
        }
        if ($this->_scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
            && $this->getRequest()->getModuleName() == 'csmarketplace') {
            return $this->_redirect('csproduct/vproducts/edit');
        }
        if (!$this->_scopeConfig->getValue(
            'ced_csmarketplace/general/ced_vproduct_activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            if ($this->getRequest()->getModuleName() == 'csproduct') {
                return $this->_redirect('csmarketplace/vproducts/edit');

            }
        }

        $currentstoreId = $this->storeManager->getStore()->getStoreId();
        $currentStoreData = $this->storeManager->getStore($currentstoreId);
        $this->registry->register('current_store_data', $currentStoreData);
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->productBuilder->build($this->getRequest());

        $vendorId = $this->customerSession->getVendorId();

        $vendorProduct = false;
        if ($productId && $vendorId) {
            $vendorProduct = $this->vproductsFactory->create()->isAssociatedProduct($vendorId, $productId);
        }

        if (!$vendorProduct) {
            return $this->_redirect('*/vproducts/index');

        }

        if (($productId && !$product->getEntityId())) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
            return $resultRedirect->setPath('*/vproducts/index');
        } elseif ($productId === 0) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Invalid product id. Should be numeric value greater than 0'));
            return $resultRedirect->setPath('*/vproducts/index');
        }

        $this->_eventManager->dispatch('catalog_product_edit_action', ['product' => $product]);
        $this->_eventManager->dispatch('csproduct_vproducts_edit_action', ['product' => $product]);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('csproduct_vproducts_' . $product->getTypeId());
        $resultPage->getConfig()->getTitle()->prepend(__('Products'));
        $resultPage->getConfig()->getTitle()->prepend($product->getName());

        if (!$this->storeManager->isSingleStoreMode()
            &&
            ($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->urlBuilder->create()->getUrl(
                        'csproduct/*/*',
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }

        $block = $resultPage->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($product->getStoreId());
        }
        return $resultPage;
    }
}
