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

use Magento\Catalog\Model\Product\Type;
use Magento\Customer\Model\Session;
use Magento\Downloadable\Model\Product\Type as downloadableType;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class NewAction
 * @package Ced\CsMarketplace\Controller\Vproducts
 */
class NewAction extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * NewAction constructor.
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
        $this->_storeManager = $storeManager;
        $this->type = $type;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor, $storeManager, $productFactory, $vproductsFactory, $type);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $allowedType = $this->type->getAllowedType($this->_storeManager->getStore()->getId());
        $resultPage = $this->resultPageFactory->create();
        $secretkey = time();
        $type = $this->getRequest()->getParam('type', $secretkey);
        if ($type == $secretkey || (in_array($type, $allowedType))) {
            $update = $resultPage->getLayout()->getUpdate();
            $update->addHandle('default');
            $resultPage->initLayout();

            switch ($type) {

                case Type::TYPE_SIMPLE :
                    $update->addHandle('csmarketplace_vproducts_simple');
                    break;
                case Type::TYPE_VIRTUAL :
                    $update->addHandle('csmarketplace_vproducts_virtual');
                    break;
                case downloadableType::TYPE_DOWNLOADABLE :
                    $update->addHandle('csmarketplace_vproducts_downloadable');
                    break;
                default:
                    $update->addHandle('csmarketplace_vproducts_type');
                    break;
            }
            $resultPage->getConfig()->publicBuild();
            $resultPage->getConfig()->getTitle()->set(__('New') . " " . __('Product'));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage('Please Select Product Type First To Create Product');
            return $this->_redirect('*/*/new');

        }
    }
}
