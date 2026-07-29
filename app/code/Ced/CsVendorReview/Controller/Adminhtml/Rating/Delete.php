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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Controller\Adminhtml\Rating;

use Magento\Backend\App\Action\Context;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsVendorReview\Model\Rating
     */
    protected $rating;

    /**
     * @var
     */
    protected $connection;

    /**
     * Delete constructor.
     * @param \Ced\CsVendorReview\Model\Rating $rating
     * @param Context $context
     */
    public function __construct(
        \Ced\CsVendorReview\Model\Rating $rating,
        \Magento\Framework\App\ResourceConnection $resource,
        Context $context
    ) {
        $this->rating = $rating;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getControllerName()) {
            case 'rating':
                return $this->ratingAcl();
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::csmarketplace');
        }
    }

    /**
     * ACL check for Rating
     *
     * @return bool
     */
    protected function ratingAcl()
    {
        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('Ced_CsVendorReview::manage_rating');
        }
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $rating = $this->rating->load($id);
            if($rating->getId() && $rating->getRatingCode()){
                $this->dropColumn($rating->getRatingCode());
            }
            $rating->delete();
            $this->messageManager->addSuccessMessage(
                __('Deleted successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $column
     */
    public function dropColumn($column)
    {
        try {
            $table = $this->_resource->getTableName('ced_csvendorreview_review');
            $connection = $this->getConnection();
            if($connection->tableColumnExists($table, $column) == true) {
                $connection->dropColumn($table, $column);
            }
            
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the rating. Please try again.'));
        }
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->_resource->getConnection(
                \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            );
        }
        return $this->connection;
    }
}
