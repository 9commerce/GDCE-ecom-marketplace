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
 * Class MassDisable
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class MassDisable extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{

    /**
     * @var \Ced\CsMarketplace\Model\VshopFactory
     */
    protected $vshopFactory;

    /**
     * MassDisable constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ced\CsMarketplace\Model\VshopFactory $vshopFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\CsMarketplace\Model\VshopFactory $vshopFactory
    ) {
        parent::__construct($context);
        $this->vshopFactory = $vshopFactory;
    }


    /**
     * Mass Disable action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $inline = $this->getRequest()->getParam('inline', 0);
        $vendorIds = $this->getRequest()->getParam('vendor_id');
        $shop_disable = $this->getRequest()->getParam('shop_disable', '');
        if ($inline) {
            $vendorIds = [$vendorIds];
        }
        if (!is_array($vendorIds)) {
            $this->messageManager->addErrorMessage(__('Please select vendor(s)'));
        } else {
            try {
                $model = $this->vshopFactory->create();
                $change = $model->saveShopStatus($vendorIds, $shop_disable);
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 shop(s) have been updated.', $change)
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('%1 An error occurred while updating the vendor(s) Shop status.', $e->getMessage())
                );
            }
        }
        $this->_redirect('*/*/index', ['_secure' => true]);
    }

}
