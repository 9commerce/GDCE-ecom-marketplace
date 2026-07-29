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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Categorytree
 * @package Ced\CsMarketplace\Controller\Vproducts
 */
class Categorytree extends \Ced\CsMarketplace\Controller\Vproducts
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Categorytree constructor.
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
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
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
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor, $storeManager, $productFactory, $vproductsFactory, $type);
        $this->resultJsonFactory = $jsonFactory;
        $this->categoryFactory = $categoryFactory;
        $this->csmarketHelper = $csmarketplaceHelper;
        $this->vproductsFactory = $vproductsFactory;
    }

    /**
     * @return bool|\Magento\Framework\Controller\Result\Json
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
        $allowed_categories = [];
        $category_mode = $this->csmarketHelper->getStoreConfig('ced_vproducts/general/category_mode', 0);
        if ($category_mode) {
            $allowed_categories = explode(',', $this->csmarketHelper
                ->getStoreConfig('ced_vproducts/general/category', 0));
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
            if (in_array($_subCategory->getId(), $category_ids)) {
                $checked = 'checked';
            }
            // phpcs:disable Magento2.Files.LineLength.MaxExceeded
            if ($childrens > 0) {
                $html .= '<li class="tree-node">';
                $html .= '<div class="tree-node-el ced-folder tree-node-collapsed">';
                $html .= '<span class="tree-node-indent"></span>';
                $html .= '<img class="tree-ec-icon tree-elbow-plus" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<img unselectable="on" class="tree-node-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= "<input class='elements' type='checkbox' name='category[]' " . $checked . " value='" .
                    $_subCategory->getId() . "'/>";
                $html .= '<span class="elements cat_name">' . $_subCategory->getName() . '(' .
                    $this->vproductsFactory->create()->getProductCount($_subCategory->getId()) . ')</span>';
                $html .= '</div>';
                $html .= '<ul class="root-category root-category-wrapper" style="display:none"></ul>';
                $html .= '</li>';
            } else {
                $html .= '<li class="tree-node">';
                $html .= '<div class="tree-node-el ced-folder tree-node-leaf">';
                $html .= '<span class="tree-node-indent"></span>';
                $html .= '<img class="tree-ec-icon tree-elbow-end" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<img unselectable="on" class="tree-node-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';
                $html .= '<input class="elements" type="checkbox" name="category[]" ' . $checked . ' value="' .
                    $_subCategory->getId() . '"/>';
                $html .= '<span class="elements cat_name">' . $_subCategory->getName() . ' (' .
                    $this->vproductsFactory->create()->getProductCount($_subCategory->getId()) . ')</span>';
                $html .= '</div>';
                $html .= '</li>';
            }
            //phpcs:enable
        }
        $resultJson->setData($html);
        return $resultJson;
    }
}
