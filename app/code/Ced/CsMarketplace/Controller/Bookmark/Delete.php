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
namespace Ced\CsMarketplace\Controller\Bookmark;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Ced\CsMarketplace\Api\BookmarkManagementInterface;
use Ced\CsMarketplace\Api\BookmarkRepositoryInterface;
use Magento\Framework\App\Action\Action;

/**
 * Class Delete action
 */
class Delete extends Action
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement
    ) {
        parent::__construct($context);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $viewIds = explode('.', $this->_request->getParam('data'));
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            array_pop($viewIds),
            $this->_request->getParam('namespace')
        );

        if ($bookmark && $bookmark->getId()) {
            $this->bookmarkRepository->delete($bookmark);
        }
    }
}
