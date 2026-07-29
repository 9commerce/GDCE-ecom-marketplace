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
 * @category   Ced
 * @package    Ced_CsVendorProductAttribute
 * @author     CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright  Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license    https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Controller\Attribute;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Edit
 * @package Ced\CsVendorProductAttribute\Controller\Attribute
 */
class Edit extends \Ced\CsVendorProductAttribute\Controller\Attribute
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\Attribute
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Backend\Model\Session $backendSessionModel
     * @param \Ced\CsVendorProductAttribute\Model\Attribute $attribute
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Model\EntityFactory $entityFactory
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Backend\Model\Session $backendSessionModel,
        \Ced\CsVendorProductAttribute\Model\Attribute $attribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\EntityFactory $entityFactory,
        \Magento\Catalog\Model\Product\Url $productUrl,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->attributeFactory = $attributeFactory;
        $this->backendSession = $backendSessionModel;
        $this->attribute = $attribute;
        parent::__construct(
            $storeManager,
            $scopeConfig,
            $entityFactory,
            $productUrl,
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $model = $this->attributeFactory->create()
            ->setEntityTypeId($this->_entityTypeId);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('csvendorproductattribute/*/');
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('csvendorproductattribute/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->backendSession->getAttributeData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $model->addData($attributeData);
        }

        $attributedata = $this->attribute->getCollection()
            ->addFieldToFilter('vendor_id', $this->_getSession()->getVendorId())
            ->addFieldToFilter('attribute_id', $id)->getData();
        if (!empty($attributedata))
            $this->registry->register('sort_order', $attributedata[0]['sort_order']);

        $this->registry->register('entity_attribute', $model);

        $item = $id ? __('Edit Product Attribute') : __('New Product Attribute');

        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Product Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }
}
