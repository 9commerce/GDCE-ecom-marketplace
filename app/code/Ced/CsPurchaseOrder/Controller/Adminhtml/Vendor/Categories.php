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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Adminhtml\Vendor;

use Magento\Backend\App\Action;

/**
 * Class Categories
 * @package Ced\CsPurchaseOrder\Controller\Adminhtml\Vendor
 */
class Categories extends \Magento\Backend\App\Action
{
    /**
     * Categories constructor.
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Action\Context $context
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Vendor Category List'), __('Vendor Category List'));
        $resultPage->addBreadcrumb(__('Vendor Category List'), __('Vendor Category List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Vendor Category List'));

        return $resultPage;
    }
}