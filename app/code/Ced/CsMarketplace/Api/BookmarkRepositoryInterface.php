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
namespace Ced\CsMarketplace\Api;

/**
 * Bookmark CRUD interface
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkRepositoryInterface
{
    /**
     * Save bookmark
     *
     * @param \Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark);

    /**
     * Retrieve bookmark
     *
     * @param int $bookmarkId
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($bookmarkId);

    /**
     * Retrieve bookmarks matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ced\CsMarketplace\Api\Data\BookmarkSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete bookmark
     *
     * @param \Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ced\CsMarketplace\Api\Data\BookmarkInterface $bookmark);

    /**
     * Delete bookmark by ID
     *
     * @param int $bookmarkId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bookmarkId);
}
