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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Delete extends \Ced\CsProduct\Controller\Vproducts
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param \Magento\Framework\App\Request\Http $http
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Context $context,
        \Magento\Framework\App\Request\Http $http,
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
        $this->vproductsFactory = $vproductsFactory;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
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
     * Execute Delete Functionality
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $vendorId = $this->_getSession()->getVendorId();
        if (!$vendorId) {
            return false;
        }

        $id = $this->getRequest()->getParam('id');
        $vendorProduct = false;
        if ($id && $vendorId) {
            $vendorProduct = $this->vproductsFactory->create()->isAssociatedProduct($vendorId, $id);
        }
        $this->registry->register('isSecureArea', true);

        $redirectBack = false;
        if (!$vendorProduct) {
            $redirectBack = true;
        } elseif ($id) {
            $product = $this->productFactory->create()->load($id);
            try {
                if ($product && $product->getId()) {
                    $product->delete();
                    $this->messageManager->addSuccessMessage(__('Your Product Has Been Sucessfully Deleted'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if ($redirectBack) {
            $this->messageManager->addErrorMessage(__('Unable to delete the product'));
        }

        return $this->_redirect('*/*/index', ['store' => $this->getRequest()->getParam('store')]);
    }
}
