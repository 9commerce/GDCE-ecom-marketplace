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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vproducts;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;


/**
 * Class MassDelete
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vproducts
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    public $mailHelper;

    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->vproductsFactory = $vproductsFactory;
        $this->productFactory = $productFactory;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getColumnValues('product_id');

        $filterRequest = $this->getRequest()->getParam('filters', null);

        $checkstatus = \Ced\CsMarketplace\Model\Vproducts::DELETED_STATUS;
        $deleted = [];
            try {
                $vendorIds = [];
            foreach ($productIds as $id) {
                    $product = $this->productFactory->create()->load($id);
                    if ($product && $product->getId()) {
                        $v_id = $this->vproductsFactory->create()->getVendorIdByProduct($product->getId());
                        $vendorIds[$v_id][] = ["name" => $product->getName(), "sku" => $product->getSku()];
                        $product->delete();
                    $deleted[]= $id;
                    }
                }
                $this->mailHelper->ProductDelete($checkstatus, $vendorIds);
                $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', count($deleted))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->_redirect->getRefererUrl();
        return $resultRedirect->setPath($url);
    }
}

