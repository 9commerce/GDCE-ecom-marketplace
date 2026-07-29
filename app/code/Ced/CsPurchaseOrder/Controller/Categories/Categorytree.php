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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Categories;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Categorytree extends \Ced\CsMarketplace\Controller\Vendor
{
    protected $categoryFactory;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
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

        $this->categoryFactory = $categoryFactory;
        $this->csmarketHelper = $csmarketplaceHelper;
    }

    /**
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $data = $this->getRequest()->getParams();
        $category_ids = isset($data["category_ids"]) ? $data["category_ids"] : [];

        $category_model = $this->categoryFactory->create()
            ->setStoreId($this->getRequest()->getParam('store', 0));
        $category = $category_model->load($data["categoryId"]);
        $children = $category->getChildren();
        $subCategoryIds = explode(",", $children);
        $result_tree = "";
        $allowed_categories = [];
        $category_mode = 0;
        $category_mode = $this->csmarketHelper->getStoreConfig('ced_vproducts/general/category_mode', 0);
        if ($category_mode) {
            $allowed_categories1 = explode(',', $this->csmarketHelper
                ->getStoreConfig('ced_vproducts/general/category', 0));
            if (!empty($allowed_categories1)) {
                foreach ($allowed_categories1 as $allowed_category) {
                    $allowed_categories = array_merge(explode('/', $allowed_category), $allowed_categories);
                }
            }
        }
        $html = '';
        foreach ($subCategoryIds as $subCategoryId) {
            $_subCategory = $category_model->load($subCategoryId);
            if ($category_mode && !in_array($subCategoryId, $allowed_categories)) {
                continue;
            }
            if ($category_mode) {
                $childrens = count(array_intersect($category_model->getResource()
                        ->getAllChildren($_subCategory), $allowed_categories)) - 1;
            } else {
                $childrens = count($category_model->getResource()
                        ->getAllChildren($_subCategory)) - 1;
            }
            $checked = "";
            if(in_array($_subCategory->getId(), $category_ids)){
                $checked = 'checked';
            }
            if ($childrens > 0) {
                $html .= '<li class="tree-node">';
                $html .= '<div class="tree-node-el ced-folder tree-node-collapsed">';
                $html .= '<span class="tree-node-indent"></span>';
                $html .= '<img class="tree-ec-icon tree-elbow-plus" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<img unselectable="on" class="tree-node-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= "<input class='elements' type='checkbox' name='category[]' ".$checked." value='" . $_subCategory->getId() . "'/>";
                $html .= '<span class="elements cat_name">' . $_subCategory->getName() .'('.$_subCategory->getId().')'.'</span>';
                $html .= '</div>';
                $html .= '<ul class="root-category root-category-wrapper" style="display:none"></ul>';
                $html .= '</li>';
            } else {
                $html .= '<li class="tree-node">';
                $html .= '<div class="tree-node-el ced-folder tree-node-leaf">';
                $html .= '<span class="tree-node-indent"></span>';
                $html .= '<img class="tree-ec-icon tree-elbow-end" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<img unselectable="on" class="tree-node-icon" src="data:image/gif;base64,R0lGODlhQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<input class="elements" type="checkbox" name="category[]" '.$checked.' value="' . $_subCategory->getId() . '"/>';
                $html .= '<span class="elements cat_name">'.$_subCategory->getName().'('.$_subCategory->getId().')'.' </span>';
                $html .= '</div>';
                $html .= '</li>';
            }
        }
        $resultJson->setData($html);
        return $resultJson;
    }
}
