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

namespace Ced\CsPurchaseOrder\Block\Categories;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Edit extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

   public function __construct(
       \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
       \Magento\Catalog\Model\Category $categoryModel,
       \Magento\Store\Model\ResourceModel\Group\Collection $groupCollection,
       \Magento\Framework\Registry $registry,
       \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
       \Magento\Customer\Model\CustomerFactory $customerFactory,
       Context $context,
       Session $customerSession,
       UrlFactory $urlFactory
   )
   {
       parent::__construct(
           $vendorFactory,
           $customerFactory,
           $context,
           $customerSession,
           $urlFactory
       );
       $this->_categoryModel = $categoryModel;
       $this->_groupCollection = $groupCollection;
       $this->_registry = $registry;
       $this->session = $customerSession;
       $this->categoryFactory = $categoryFactory;

   }

    public function getProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get Category IDs
     *
     */
    public function getCategoryIds()
    {
        $vendorId = $this->session->getVendorId();
        $category_ids = $this->categoryFactory->create()
            ->addFieldToFilter('vendor_id',$vendorId)
            ->getColumnValues('category_id');
        return $category_ids;
    }

    public function getCategoryModel(){
        return $this->_categoryModel;
    }

    public function getGroups(){
        return $this->_groupCollection->addFieldToFilter('group_id', array('neq'=>0))->setOrder('website_id', 'ASC');
    }

    public function getBackUrl()
    {
        return $this->getUrl('cspurchaseorder/categories/save');
    }

}