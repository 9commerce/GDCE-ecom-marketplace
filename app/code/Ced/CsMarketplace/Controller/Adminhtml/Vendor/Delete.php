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

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class Delete extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    protected $mailHelper;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Helper\Mail $mailHelper
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->mailHelper = $mailHelper;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function execute()
    {
        $vendorId = (int)$this->getRequest()->getParam('vendor_id');
        $vendor = $this->vendorFactory->create()->load($vendorId);
        if ($vendor && $vendor->getId()) {
            try {
                $this->vproductsFactory->create()->deleteVendorProducts($vendor->getId());
                $this->mailHelper
                    ->sendAccountEmail(\Ced\CsMarketplace\Model\Vendor::VENDOR_DELETED_STATUS,$vendor);
                $vendor->delete();
                $this->messageManager->addSuccessMessage(__('The vendor has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }
}
