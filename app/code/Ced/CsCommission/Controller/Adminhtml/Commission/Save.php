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

class Save extends Action
{
    /**
     * @var \Ced\CsCommission\Model\Commission
     */
    protected $commission;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Ced\CsCommission\Model\Commission $commission
     * @param \Magento\Backend\Model\Session $session
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsCommission\Model\Commission $commission,
        \Magento\Backend\Model\Session $session
    ) {
        parent::__construct($context);
        $this->commission = $commission;
        $this->session = $session;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->commission;

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            if ($type = $this->_session->getCedtype()) {
                $data['type'] = $type;
                $data['type_id'] = $this->_session->getCedtypeid();
            }
            if ($vendorId = $this->_session->getCedVendorId()) {
                $data['vendor'] = $vendorId;
            }

            switch ($data['method']) {
                case "fixed":
                    $data['fee'] = round($data['fee'], 2);
                    break;
                case "percentage":
                    $data['fee'] = min((int)$data['fee'], 100);
                    break;
            }
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('The Commission Has been Saved.'));
                $this->session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('popup')) {
                        $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true, 'popup' => true]);
                    } else {
                        $this->_redirect('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    }
                    return;
                }
                if ($this->getRequest()->getParam('popup')) {
                    $this->_redirect('*/*/', ['popup' => true]);
                } else {
                    $this->_redirect('*/*/');
                }
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Commission.'));
            }

            $this->_getSession()->setFormData($data);

            if ($this->getRequest()->getParam('popup')) {
                $this->_redirect('*/*/', ['id' => $this->getRequest()->getParam('id'), 'popup' => true]);
            } else {
                $this->_redirect('*/*/', ['id' => $this->getRequest()->getParam('id')]);
            }
            return;
        }

        if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/', ['popup' => true]);
        } else {
            $this->_redirect('*/*/');
        }
    }
}
