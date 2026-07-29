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
namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModel;

class MassDelete extends \Ced\CsProduct\Controller\Vproducts
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * MassDelete constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $http,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Context $context,
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
        ResourceModel $productsResource,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->registry = $registry;
        $this->vproductsFactory = $vproductsFactory;
        $this->productFactory = $productFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productsResource = $productsResource;
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
     * Execute Function
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $vendorId = $this->_getSession()->getVendorId();
        if (!$vendorId) {
            return $this->_redirect('csproduct/*/index', ['store' => $this->getRequest()->getParam('store')]);
        }

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $entity_ids = $collection->getAllIds();

        $this->registry->register('isSecureArea', true);
        $redirectBack = false;

        if (!is_array($entity_ids) || empty($entity_ids)) {
            $this->messageManager->addErrorMessage(__('Please select Products(s).'));
        } else {
            $productDeleted = 0;
            try {
                foreach ($collection as $collect) {
                    $product_id = $collect->getProductId();
                    $model = $this->productFactory->create();
                    $this->productsResource->load($model, $product_id);
                    if ($model->getId()) {
                        $model->delete();
                        $productDeleted++;
                    }
                }
            } catch (\Exception $e) {
                $redirectBack = true;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $productDeleted)
        );
        return $this->_redirect('csproduct/*/index', ['store' => $this->getRequest()->getParam('store')]);
    }
}
