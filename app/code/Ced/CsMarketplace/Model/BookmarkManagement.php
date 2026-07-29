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

namespace Ced\CsMarketplace\Model;
use Ced\CsMarketplace\Model\Session as MarketplaceSession;
class BookmarkManagement implements \Ced\CsMarketplace\Api\BookmarkManagementInterface
{
    /**
     * @var \Ced\CsMarketplace\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Ced\CsMarketplace\Api\BookmarkRepositoryInterface $bookmarkRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Ced\CsMarketplace\Api\BookmarkRepositoryInterface $bookmarkRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        MarketplaceSession $userContext
    ) {
        $this->bookmarkRepository = $bookmarkRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function loadByNamespace($namespace)
    {
        $userIdFilter = $this->filterBuilder
            ->setField('user_id')
            ->setConditionType('eq')
            ->setValue($this->userContext->getVendorId())
            ->create();
        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue($namespace)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$userIdFilter]);
        $this->searchCriteriaBuilder->addFilters([$namespaceFilter]);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->bookmarkRepository->getList($searchCriteria);

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifierNamespace($identifier, $namespace)
    {

        $userIdFilter = $this->filterBuilder
            ->setField('user_id')
            ->setConditionType('eq')
            ->setValue($this->userContext->getVendorId())
            ->create();
        $identifierFilter = $this->filterBuilder
            ->setField('identifier')
            ->setConditionType('eq')
            ->setValue($identifier)
            ->create();
        $namespaceFilter = $this->filterBuilder
            ->setField('namespace')
            ->setConditionType('eq')
            ->setValue($namespace)
            ->create();

        $this->searchCriteriaBuilder->addFilters([$userIdFilter]);
        $this->searchCriteriaBuilder->addFilters([$identifierFilter]);
        $this->searchCriteriaBuilder->addFilters([$namespaceFilter]);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->bookmarkRepository->getList($searchCriteria);

        if ($searchResults->getTotalCount() > 0) {
            foreach ($searchResults->getItems() as $searchResult) {
                $bookmark = $this->bookmarkRepository->getById($searchResult->getId());
                return $bookmark;
            }
        }

        return null;
    }
}
