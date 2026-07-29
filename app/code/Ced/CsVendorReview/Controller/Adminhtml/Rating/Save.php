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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Controller\Adminhtml\Rating;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Ced\CsVendorReview\Model\Rating
     */
    protected $rating;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $cacheManager;

    /**
     * @var
     */
    protected $connection;

    /**
     * Save constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Ced\CsVendorReview\Model\Rating $rating
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Ced\CsVendorReview\Model\Rating $rating,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        Context $context
    ) {
        $this->_resource = $resource;
        $this->rating = $rating;
        $this->session = $session;
        $this->cacheManager = $cacheManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->rating;
            if($data['rating_code']){
                $data['rating_code'] = strtolower($data['rating_code']);
            }
            
            $id = $this->getRequest()->getParam('id');
            if ($id) {       //Add Custom Rating Edit Validation
                $model->load($id);
                if ($model->getRatingCode() == $data['rating_code']) {
                    $model->setData($data);
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('Rating Criteria Has been Saved.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Sorry Rating Code Not Editable'));
                }

                $this->session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;

            } else {
                $collection = $model->getCollection()->addFieldToFilter('rating_code', $data['rating_code']);
                $model->setData($data);
                try {
                    if (count($collection) < 1) {
                        $this->addColumn($data['rating_code']);
                        $this->messageManager->addSuccessMessage(__('Rating Criteria Has been Saved.'));
                        $model->save();

                        //Here we add a Custom DDL Cache Flush Code
                        $this->cacheManager->clean([\Magento\Framework\DB\Adapter\DdlCache::TYPE_IDENTIFIER]);
                    } else {
                        $this->messageManager->addErrorMessage(__('Rating Item with the same code already exists.'));
                    }
                    $this->session->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Somethinggg went wrong while saving the rating.')
                    );
                }
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $column
     */
    public function addColumn($column)
    {
        try {
            $table = $this->_resource->getTableName('ced_csvendorreview_review');

            $this->getConnection()->addColumn(
                $table,
                $column,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => false,
                    'comment' => $column
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the rating.'));
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
}
