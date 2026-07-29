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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Ced\CsMarketplace\Api\BookmarkRepositoryInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Ced\CsMarketplace\Api\Data\BookmarkInterface;
use Ced\CsMarketplace\Model\ResourceModel\Bookmark\Collection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class BookmarkRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookmarkRepository implements BookmarkRepositoryInterface
{
    /**
     * @var \Ced\CsMarketplace\Api\Data\BookmarkInterfaceFactory
     */
    protected $cedbookmarkFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Bookmark
     */
    protected $cedbookmarkResourceModel;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory
     */
    protected $cedsearchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $cedcollectionProcessor;

    /**
     * @param \Ced\CsMarketplace\Api\Data\BookmarkInterfaceFactory $cedbookmarkFactory
     * @param Bookmark $cedbookmarkResourceModel
     * @param \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory $cedsearchResultsFactory
     * @param CollectionProcessorInterface | null $cedcollectionProcessor
     */
    public function __construct(
        \Ced\CsMarketplace\Api\Data\BookmarkInterfaceFactory $cedbookmarkFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Bookmark $cedbookmarkResourceModel,
        \Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory $cedsearchResultsFactory,
        ?CollectionProcessorInterface $cedcollectionProcessor = null
    ) {

        $this->cedbookmarkResourceModel = $cedbookmarkResourceModel;
        $this->cedbookmarkFactory = $cedbookmarkFactory;
        $this->cedsearchResultsFactory = $cedsearchResultsFactory;
        $this->cedcollectionProcessor = $cedcollectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save bookmark.
     *
     * @param BookmarkInterface $bookmark
     * @return BookmarkInterface
     * @throws CouldNotSaveException
     */
    public function save(BookmarkInterface $bookmark)
    {
        try {
            $this->cedbookmarkResourceModel->save($bookmark);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $bookmark;
    }

    /**
     * Retrieve bookmark.
     *
     * @param int $bookmarkId
     * @return BookmarkInterface
     * @throws NoSuchEntityException
     */
    public function getById($bookmarkId)
    {
        $bookmark = $this->cedbookmarkFactory->create();
        $this->cedbookmarkResourceModel->load($bookmark, $bookmarkId);
        if (!$bookmark->getId()) {
            throw new NoSuchEntityException(
                __('The bookmark with "%1" ID doesn\'t exist. Verify your information and try again.', $bookmarkId)
            );
        }
        return $bookmark;
    }

    /**
     * Retrieve bookmarks matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->cedsearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        /** @var \Ced\CsMarketplace\Model\ResourceModel\Bookmark\Collection $collection */
        $collection = $this->cedbookmarkFactory->create()->getCollection();
        $this->cedcollectionProcessor->process($searchCriteria, $collection);
        $searchResults->setTotalCount($collection->getSize());
        $bookmarks = [];
        /** @var BookmarkInterface $bookmark */
        foreach ($collection->getItems() as $bookmark) {
            $bookmarks[] = $this->getById($bookmark->getId());
        }
        $searchResults->setItems($bookmarks);
        return $searchResults;
    }

    /**
     * Delete bookmark.
     *
     * @param BookmarkInterface $bookmark
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(BookmarkInterface $bookmark)
    {
        try {
            $this->cedbookmarkResourceModel->delete($bookmark);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete bookmark by ID.
     *
     * @param int $bookmarkId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($bookmarkId)
    {
        return $this->delete($this->getById($bookmarkId));
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 101.0.0
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->cedcollectionProcessor) {
            $this->cedcollectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CollectionProcessorInterface::class
            );
        }
        return $this->cedcollectionProcessor;
    }
}
