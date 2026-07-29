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

class Edit extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
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
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor, $storeManager, $productFactory, $vproductsFactory, $type);
    }

    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $vendorId = $this->_getSession()->getVendorId();
        $vendorProduct = 0;
        if ($id && $vendorId) {
            $vendorProduct = $this->vproductsFactory->create()
                ->isAssociatedProduct($vendorId, $id);
        }

        if (!$vendorProduct) {
            $this->_redirect('csmarketplace/vproducts/index');
            return;
        }

        if ($type = $this->getRequest()->getParam('type')) {
            $resultPage = $this->resultPageFactory->create();
            $update = $resultPage->getLayout()->getUpdate();
            $update->addHandle('default');
            $resultPage->initLayout();

            switch ($type) {
                case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE :
                    $update->addHandle('csmarketplace_vproducts_simple');
                    break;
                case \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL :
                    $update->addHandle('csmarketplace_vproducts_virtual');
                    break;
                case \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE :
                    $update->addHandle('csmarketplace_vproducts_downloadable');
                    break;
                default:
                    $this->_redirect('csmarketplace/vproducts/index');
                    break;
            }
            $resultPage->getConfig()->publicBuild();
            $resultPage->getConfig()->getTitle()->set(__('Edit') . " " . __('Product'));
            return $resultPage;
        } else {
            $this->_redirect('csmarketplace/vproducts/index');
        }
    }
}
