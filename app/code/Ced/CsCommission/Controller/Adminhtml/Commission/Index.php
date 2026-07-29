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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Controller\Adminhtml\Commission;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_session = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            $this->resultPage->getLayout()->getUpdate()->addHandle('ced_popup');

            if ($this->getRequest()->getParam('type') || $this->getRequest()->getParam('id') || $this->getRequest()
                    ->getParam('vendor_id')) {
                if ($this->getRequest()->getParam('type')) {
                    $type = $this->getRequest()->getParam('type');
                } else {
                    $type = 'default';
                }

                if ($this->getRequest()->getParam('id')) {
                    $id = $this->getRequest()->getParam('id');
                } else {
                    $id = 0;
                }

                if ($this->getRequest()->getParam('vendor_id')) {
                    $vendor_id = $this->getRequest()->getParam('vendor_id');
                } else {
                    $vendor_id = 0;
                }
            } else {
                if ($this->_session->getCedtype()) {
                    $type = $this->_session->getCedtype();
                } else {
                    $type = 'default';
                }
                if ($this->_session->getCedtypeid()) {
                    $id = $this->_session->getCedtypeid();
                } else {
                    $id = 0;
                }
                if ($this->_session->getCedVendorId()) {
                    $vendor_id = $this->_session->getCedVendorId();
                } else {
                    $vendor_id = 0;
                }
            }
            $this->_session->setCedtype($type);
            $this->_session->setCedtypeid($id);
            $this->_session->setCedVendorId($vendor_id);

        } else {
            $this->resultPage->setActiveMenu('Ced_Commission::commission');
            $this->resultPage->getConfig()->getTitle()->set((__('Category Wise Commission')));
        }

        return $this->resultPage;
    }
}
