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
 * @category  Ced
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Controller\Attribute;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Delete
 * @package Ced\CsVendorProductAttribute\Controller\Attribute
 */
class Delete extends \Ced\CsVendorProductAttribute\Controller\Attribute
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\Attribute
     */
    protected $attribute;

    /**
     * Delete constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $eavAttributeFactory
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
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $eavAttributeFactory,
        \Ced\CsVendorProductAttribute\Model\Attribute             $attribute,
        \Magento\Store\Model\StoreManagerInterface                $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig,
        \Magento\Eav\Model\EntityFactory                          $entityFactory,
        \Magento\Catalog\Model\Product\Url                        $productUrl,
        Context                                                   $context,
        \Magento\Framework\View\Result\PageFactory                $resultPageFactory,
        Session                                                   $customerSession,
        UrlFactory                                                $urlFactory,
        \Magento\Framework\Registry                               $registry,
        \Magento\Framework\Controller\Result\JsonFactory          $jsonFactory,
        \Ced\CsMarketplace\Helper\Data                            $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl                             $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory                    $vendor
    )
    {
        $this->attributeFactory = $eavAttributeFactory;
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
     * Delete the Attribute
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->attributeFactory->create();

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('We can\'t delete the attribute.'));
                return $this->_redirect('csvendorproductattribute/attribute/index');
            }

            try {
                $model->delete();
                //delete entry from vendor table
                $vendor_attr_model = $this->attribute->getCollection()
                    ->addFieldToFilter('attribute_id', $id)->getFirstItem();
                $vendor_attr_model->delete();
                //End
                $this->messageManager->addSuccessMessage(__('You deleted the product attribute.'));

                return $this->_redirect('csvendorproductattribute/attribute/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    'csvendorproductattribute/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an attribute to delete.'));

        return $this->_redirect('csvendorproductattribute/attribute/index');
    }
}
