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

use Magento\Backend\App\Action;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsVendorReview\Model\Rating
     */
    protected $rating;

    /**
     * MassStatus constructor.
     * @param Action\Context $context
     * @param \Ced\CsVendorReview\Model\Rating $rating
     */
    public function __construct(Action\Context $context, \Ced\CsVendorReview\Model\Rating $rating)
    {
        $this->rating = $rating;
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
        $ids = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select rating(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $this->rating->load($id)->setData('status', $status)->save();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been updated.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}
