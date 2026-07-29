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
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsMarketplace\Model\Vproducts;
/**
 * Class MassDelete
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vproducts
 */
class MassDelete extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper,
        \Magento\Catalog\Model\ProductRepository $productFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        $this->filter = $filter;
        $this->vproductsFactory = $vproductsFactory;
        $this->mailHelper = $mailHelper;
        $this->productRepository = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->formKey = $formKey;
        $this->registry = $registry;
        $this->request->setParam('form_key', $this->formKey->getFormKey());

        parent::__construct(
            $context, $resultPageFactory,$customerSession, $urlFactory, $registry,$jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor
        );

    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        if($collectionSize){
            $this->registry->register("isSecureArea", 1);
            $productDeleted = 0;
            $vendorIds = [];
            try{
                foreach ($collection as $selected) {
                    $product = $this->productRepository->getById($selected->getProductId());
                    $vproductsObj = $this->vproductsFactory->create();
                    $vproduct = $vproductsObj->load($product->getId(),'product_id');
                    if (count($product->getData()) && $product->getId() && count($vproduct->getData()) && $vproduct->getId()) {
                        if($v_id = $vproduct->getVendorId()){
                            $vendorIds[$v_id][] = ["name" => $product->getName(), "sku" => $product->getSku()];
                            $this->productRepository->delete($product);
                            $vproduct->delete();
                            $productDeleted++;
                        }
                    }
                }
                $this->mailHelper->ProductDelete(Vproducts::DELETED_STATUS, $vendorIds);

            }catch(\Exception $e){
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
            if ($productDeleted) {
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $productDeleted));
            }
        }else{
            $this->messageManager->addErrorMessage(__('Unable to delete the product'));
        }
        return $this->_redirect('*/*/');

    }
}

