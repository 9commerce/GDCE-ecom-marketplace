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
 * Class MassDelete
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class MassDelete extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
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
     * MassDelete constructor.
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
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $vendorIds = $this->getRequest()->getParam('vendor_id');
        if (!is_array($vendorIds)) {
            $this->messageManager->addErrorMessage(__('Please select vendor(s).'));
        } else {
            if (!empty($vendorIds)) {
                try {
                    foreach ($vendorIds as $vendorId) {
                        $vendor = $this->vendorFactory->create()->load($vendorId);
                        $this->_eventManager->dispatch('csmarketplace_controller_adminhtml_vendor_delete',
                            ['vendor' => $vendor]);
                        $this->vproductsFactory->create()->deleteVendorProducts($vendorId);
                        $this->mailHelper
                            ->sendAccountEmail(\Ced\CsMarketplace\Model\Vendor::VENDOR_DELETED_STATUS,$vendor);
                        $vendor->delete();
                    }
                    $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been deleted.',
                        count($vendorIds)));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
        return $this->_redirect('*/*/index');

    }
}
