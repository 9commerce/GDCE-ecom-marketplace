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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vendor;

/**
 * Class Edit
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class Edit extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Backend\Model\Session $session
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->vendorFactory = $vendorFactory;
        $this->session = $session;
    }

    /**
     * Edit action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('vendor_id');
        $model = $this->vendorFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Vendor no longer exists.'));
                $this->_redirect('csmarketplace/vendor/index');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('vendor_data', $model);
        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Vendor') : __('New Vendor'), $id ? __('Edit Vendor') : __('New Vendor'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Vendor')
        );

        $this->_view->renderLayout();
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Ced_CsMarketplace::csmarketplace'
        )->_addBreadcrumb(
            __('Manage Vendor'),
            __('Vendor')
        );
        return $this;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_CsMarketplace::csmarketplace');
    }
}
