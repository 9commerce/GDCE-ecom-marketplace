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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Categories;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsPurchaseOrder\Controller\Categories
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Ced\CsPurchaseOrder\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Save constructor.
     * @param \Ced\CsPurchaseOrder\Model\CategoryFactory $categoryFactory
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory $collectionFactory
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
        \Ced\CsPurchaseOrder\Model\CategoryFactory $categoryFactory,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
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

        parent::__construct(
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
        $this->session = $customerSession;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $postdata = $this->getRequest()->getPostValue();
        if (isset($postdata['category_ids'])) {
            $posted_cids = explode(',', $postdata['category_ids']);
        }
        $vendorId = $this->session->getVendorId();
        $category_ids = $this->collectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId);

        if ($category_ids) {
            foreach ($category_ids as $category) {
                if (!in_array($category->getCategoryId(), $posted_cids)) {

                    $this->categoryFactory->create()->load($category->getId())->delete();
                }
            }
        }
        if ($posted_cids && $vendorId) {
            foreach ($posted_cids as $category) {

                $category_ids = $this->collectionFactory->create()
                    ->addFieldToFilter('vendor_id', $vendorId)
                    ->getColumnValues('category_id');

                if (in_array($category, $category_ids)) {
                    continue;
                }

                $model = $this->categoryFactory->create();
                $model->setVendorId($vendorId)->setCategoryId($category)->save();
            }
            $this->messageManager->addSuccessMessage(__('Categories Have Been Saved Successfully'));
            return $this->_redirect('*/*/assigned');

        } else {
            $this->messageManager->addErrorMessage(__('Something Went Wrong While Saving Request'));
            return $this->_redirect('*/*/assigned');
        }
    }
}
