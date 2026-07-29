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
 * @author    CedCommerce Core Team <connect@cedcommerce.com >demo
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Controller\Adminhtml\Commission;

use Magento\Backend\App\Action;
use Ced\CsCommission\Model\Commission;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Commission
     */
    protected $commission;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Commission $commission
     */
    public function __construct(
        Action\Context $context,
        Commission $commission
    ) {
        parent::__construct($context);
        $this->commission = $commission;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $banner = $this->commission->load($id);
            $banner->delete();
            $this->messageManager->addSuccessMessage(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        if ($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/', ['popup' => true]);
        } else {
            $this->_redirect('*/*/');
        }
    }
}
