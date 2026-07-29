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

namespace Ced\CsMarketplace\Model\System\Config\Source\Vproducts;

/**
 * Class CategoryCollection
 * @package Ced\CsMarketplace\Model\System\Config\Source\Vproducts
 */
class CategoryCollection
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var
     */
    private $options;

    /**
     * CategoryCollection constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [];
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'level', 'children'])
            ->addAttributeToFilter('entity_id', ['neq' => \Magento\Catalog\Model\Category::TREE_ROOT_ID]);

        $categoryById = [
            \Magento\Catalog\Model\Category::TREE_ROOT_ID => [
                'id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['level'] = $category->getLevel();
            $categoryById[$category->getId()]['path'] = $category->getPath();
            $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
        }

        $this->renederCat($categoryById[\Magento\Catalog\Model\Category::TREE_ROOT_ID]['children']);
        return $this->options;
    }

    /**
     * @param $data
     * @param int $level
     */
    public function renederCat($data, int $level = 0)
    {
        foreach ($data as $cat) {
            $arrow = str_repeat("---", $level);
            $this->options[] = array('value' => $cat['id'], 'label' => __($arrow." ".$cat['label']));
            if ($cat['children']) {
                $this->renederCat($cat['children'], $level+1);
            }
        }
    }
}
