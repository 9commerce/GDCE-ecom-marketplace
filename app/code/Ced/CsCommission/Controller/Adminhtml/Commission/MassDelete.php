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

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsCommission\Model\Commission
     */
    protected $commission;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param \Ced\CsCommission\Model\Commission $commission
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsCommission\Model\Commission $commission
    ) {
        parent::__construct($context);
        $this->commission = $commission;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $row = $this->commission->load($id);
                    $row->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/', ['popup' => true]);
        } else {
            $this->_redirect('*/*/');
        }
    }
}
