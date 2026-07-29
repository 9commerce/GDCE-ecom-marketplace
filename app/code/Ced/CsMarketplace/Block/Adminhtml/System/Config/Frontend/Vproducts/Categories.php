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


namespace Ced\CsMarketplace\Block\Adminhtml\System\Config\Frontend\Vproducts;

use Magento\Catalog\Block\Adminhtml\Category\Tree;
use Magento\Framework\Data\Tree\Node;

/**
 * Class Categories
 * @package Ced\CsMarketplace\Block\Adminhtml\System\Config\Frontend\Vproducts
 */
class Categories extends Tree
{

    /**
     * @var null
     */
    protected $_selectedNodes = null;

    /**
     * Forms string out of getCategoryIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    /**
     * Return array with category IDs which the product is assigned to
     *
     * @return array
     */
    protected function getCategoryIds()
    {
        $values = $this->_scopeConfig->getValue('ced_vproducts/general/category');
        $values = trim($values, ',');
        return explode(',', $values);
    }

    /**
     * Returns root node and sets 'checked' flag (if necessary)
     *
     * @return Node|mixed
     */
    public function getRootNode()
    {
        $root = $this->getRoot();
        if ($root && in_array($root->getId(), $this->getCategoryIds()))
            $root->setChecked(true);

        return $root;
    }

    /**
     * Returns root node
     *
     * @param \Magento\Catalog\Model\Category|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|mixed
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if (($parentNodeCategory !== null) && $parentNodeCategory->getId())
            return $this->getNode($parentNodeCategory, $recursionLevel);

        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            if ($storeId) {
                $store = $this->getStore();
                $rootId = $store->getRootCategoryId();
            }

            $ids = $this->getSelectedCategoriesPathIds($rootId);
            $tree = $this->_categoryTree->create()->loadByIds($ids, false, false);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
                if ($this->isReadonly())
                    $root->setDisabled(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }

    /**
     * Return distinct path ids of selected categories
     *
     * @param mixed $rootId Root category Id for context
     * @return array
     */
    public function getSelectedCategoriesPathIds($rootId = false)
    {
        $ids = [];
        $categoryIds = $this->getCategoryIds();
        if (empty($categoryIds))
            return [];

        $collection = $this->_categoryFactory->create()->getCollection();
        if ($rootId) {
            $collection->addFieldToFilter('parent_id', $rootId);
        } else {
            $collection->addFieldToFilter('entity_id', ['in' => $categoryIds]);
        }

        foreach ($collection as $item) {
            if ($rootId && !in_array($rootId, $item->getPathIds()))
                continue;

            foreach ($item->getPathIds() as $id) {
                if (!in_array($id, $ids)) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Returns URL for loading tree
     *
     * @param null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('csmarketplace/adminhtml_vproducts/categoriesJson', ['_current' => true]);
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return (!$this->_storeManager->isSingleStoreMode()) ? false : true;
    }

    /**
     * Returns array with configuration of current node
     *
     * @param $node
     * @param int $level How deep is the node in the tree
     * @return string
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = parent::_getNodeJson($node, $level);

        if ($this->_isParentSelectedCategory($node)) {
            $item['expanded'] = true;
        }
        if (in_array($node->getId(), $this->getCategoryIds())) {

            $item['checked'] = true;
        }

        if ($this->isReadonly()) {
            $item['disabled'] = true;
        }

        return $item;
    }

    /**
     * Returns whether $node is a parent (not exactly direct) of a selected node
     *
     * @param Node $node
     * @return bool
     */
    protected function _isParentSelectedCategory($node)
    {
        $result = false;
        // Contains string with all category IDs of children (not exactly direct) of the node
        $allChildren = $node->getAllChildren();
        if ($allChildren) {
            $selectedCategoryIds = $this->getCategoryIds();
            $allChildrenArr = explode(',', $allChildren);
            for ($i = 0, $cnt = count($selectedCategoryIds); $i < $cnt; $i++) {
                $isSelf = $node->getId() == $selectedCategoryIds[$i];
                if (!$isSelf && in_array($selectedCategoryIds[$i], $allChildrenArr)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns array with nodes those are selected (contain current product)
     *
     * @return array
     */
    protected function _getSelectedNodes()
    {
        if ($this->_selectedNodes === null) {
            $this->_selectedNodes = [];
            $root = $this->getRoot();
            foreach ($this->getCategoryIds() as $categoryId) {
                if ($root) {
                    $this->_selectedNodes[] = $root->getTree()->getNodeById($categoryId);
                }
            }
        }

        return $this->_selectedNodes;
    }
}
