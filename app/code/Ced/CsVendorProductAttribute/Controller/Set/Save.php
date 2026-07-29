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

namespace Ced\CsVendorProductAttribute\Controller\Set;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsVendorProductAttribute\Controller\Set
 */
class Save extends \Ced\CsVendorProductAttribute\Controller\Set
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var
     */
    protected $_url;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $setFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\AttributesetFactory
     */
    protected $attributesetFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Save constructor.
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Ced\CsVendorProductAttribute\Model\AttributesetFactory $attributesetFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
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
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Ced\CsVendorProductAttribute\Model\AttributesetFactory $attributesetFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->setFactory = $setFactory;
        $this->filterManager = $filterManager;
        $this->attributesetFactory = $attributesetFactory;
        parent::__construct(
            $storeManager,
            $scopeConfig,
            $productFactory,
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
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if ($this->registry->registry('entityType') === null) {
            $this->_setTypeId();
        }
        return $this->registry->registry('entityType');
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $vendor_id = $this->customerSession->getVendorId();
        if (!$vendor_id) {
            return false;
        }

        $entityTypeId = $this->_getEntityTypeId();
        $hasError = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $model = $this->setFactory->create()->setEntityTypeId($entityTypeId);

        try {
            if ($isNewSet) {
                //filter html tags
                $name = $this->filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));
            } else {

                if ($attributeSetId) {
                    $model->load($attributeSetId);
                }
                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This attribute set no longer exists.')
                    );
                }

                $name = $this->filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));

                $model->setAttributeSetName(trim($name));
            }

            $model->validate();
            if ($isNewSet) {
                $model->save();
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->save();

            //Save Data in Vendor Table
            $attr_set_model = $this->attributesetFactory->create();
            $attribute_set_id = $model->getId();
            $vendordata['attribute_set_id'] = $attribute_set_id;
            $attribute_set_model = $model->load($attribute_set_id);

            $vendordata['attribute_set_code'] = $attribute_set_model->getAttributeSetName();

            //save data only for new attribute
            if ($isNewSet) {
                $vendordata['vendor_id'] = $vendor_id;
                $attr_set_model->setData($vendordata);
                $attr_set_model->save();
            } else {
                $attr_set_model->load($vendordata['attribute_set_id'], 'attribute_set_id');
                $attr_set_model->setAttributeSetCode($vendordata['attribute_set_code'])->save();
            }
            //End

            $this->messageManager->addSuccessMessage(__('You saved the attribute set.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attribute set.'));
            $hasError = true;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));
                $body = [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'id' => $model->getId(),
                ];
                return $this->resultJsonFactory->create()->setData($body);
            } else {
                if ($hasError) {
                    $resultRedirect->setPath('csvendorproductattribute/*/add');
                } else {
                    $resultRedirect->setPath('csvendorproductattribute/*/');
                }
                return $resultRedirect;
            }
        } else {
            $response = [];
            if ($hasError) {
                $layout = $this->layoutFactory->create();
                $layout->initMessages();
                $response['error'] = 1;
                $response['message'] = $layout->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error'] = 0;
                $response['url'] = $this->_url->getUrl('csvendorproductattribute/*/');
            }
            $resultRedirect->setPath('csvendorproductattribute/*/');
            return $resultRedirect;
        }
    }
}
