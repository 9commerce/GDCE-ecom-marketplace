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
use Ced\CsMarketplace\Model\VproductsFactory;
/**
 * Updates status for a batch of products.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var object
     */
    protected $collectionFactory;

    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        VproductsFactory $vproductsFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->vproductsFactory = $vproductsFactory;

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
        $checkstatus  = (int) $this->getRequest()->getParam('status');

        if (!is_array($productIds)) {
            $this->messageManager->addErrorMessage(__('Please select products(s).'));
        } elseif (!empty($productIds) && $checkstatus !=='') {
            try {
                $errors = $this->vproductsFactory->create()->changeVproductStatus($productIds, $checkstatus);
                if ($errors['success']) {
                    $this->messageManager->addSuccessMessage(__("Status changed Successfully"));
                }
                if ($errors['error']) {
                    $this->messageManager->addErrorMessage(__('Can\'t process approval/disapproval for some products.Some of Product\'s vendor(s) are disapproved or not exist.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('%1', $e->getMessage()));
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->_redirect->getRefererUrl();
        return $resultRedirect->setPath($url);
    }
}
