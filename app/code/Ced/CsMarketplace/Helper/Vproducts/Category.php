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
namespace Ced\CsMarketplace\Helper\Vproducts;

use Magento\Catalog\Api\CategoryRepositoryInterface;


/**
 * Class Category
 * @package Ced\CsMarketplace\Helper\Vproducts
 */
class Category extends \Magento\Catalog\Helper\Category
{

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Category constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\CollectionFactory $dataCollectionFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        CategoryRepositoryInterface $categoryRepositoryInterface
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $categoryFactory, $storeManager, $dataCollectionFactory,
            $categoryRepositoryInterface);
    }

    /**
     * Retrieve current store categories
     * @param int $parentId
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCategories($parentId = 0, $sorted = false, $asCollection = false, $toLoad = true)
    {
        $parent = $parentId;
        $categoryObj = $this->categoryRepository->get($parent);
        $subcategories = $categoryObj->getChildrenCategories();
        return $subcategories;
    }
}
