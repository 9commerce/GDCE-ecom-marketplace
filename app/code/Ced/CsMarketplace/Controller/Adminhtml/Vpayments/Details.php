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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vpayments;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Details
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vpayments
 */
class Details extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\CsMarketplace\Model\VpaymentFactory
     */
    protected $vpaymentFactory;

    /**
     * Details constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        \Ced\CsMarketplace\Model\VpaymentFactory $vpaymentFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->vpaymentFactory = $vpaymentFactory;
    }

    /**
     * Details action
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $rowId = $this->getRequest()->getParam('id');
        $row = $this->vpaymentFactory->create()->load($rowId);
        if (!$row->getId()) {
            return $this->_redirect('*/*/', ['_secure' => true]);
        }

        $this->_coreRegistry->register('csmarketplace_current_transaction', $row);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_CsMarketplace::vendor_transaction');
        $resultPage->addBreadcrumb(__('CsMarketplace'), __('CsMarketplace'));
        $resultPage->addBreadcrumb(__('Manage Vendor Transactions'), __('Manage Vendor Transactions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Vendor Transactions'));
        return $resultPage;
    }
}
