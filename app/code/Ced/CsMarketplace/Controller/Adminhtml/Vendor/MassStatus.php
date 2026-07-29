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

use Magento\Framework\App\Action\Context;

/**
 * Class MassStatus
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class MassStatus extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ced\CsMarketplace\Model\VshopFactory
     */
    protected $vshopFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\VshopFactory $vshopFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Model\VshopFactory $vshopFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    ) {
        $this->vshopFactory = $vshopFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * Mass Status action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {

        $inline = $this->getRequest()->getParam('inline', 0);
        $vendorIds = $this->getRequest()->getParam('vendor_id');
        $status = $this->getRequest()->getParam('status', '');
        $reason = $this->getRequest()->getParam('reason', '');

        $shop_disable = 2;
        if ($status == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
            $shop_disable = 1;
        }
        if ($status == \Ced\CsMarketplace\Model\Vendor::VENDOR_DISAPPROVED_STATUS) {
            $shop_disable = 2;
        }

        if ($inline) {
            $vendorIds = [$vendorIds];
        } else {
            $vendorIds = explode(',', $vendorIds);
        }

        if (!is_array($vendorIds)) {
            $this->messageManager->addErrorMessage(__('Please select vendor(s)'));
        } else {
            try {
                $model = $this->_validateMassStatus($vendorIds, $status);
                if ($reason) {
                    $model->saveMassAttribute($vendorIds, ['code' => 'reason', 'value' => $reason]);
                } else {
                    $model->saveMassAttribute($vendorIds, ['code' => 'reason', 'value' => '']);
                }
                $model->saveMassAttribute($vendorIds, ['code' => 'status', 'value' => $status]);

                $shop_model = $this->vshopFactory->create();
                $shop_model->saveShopStatus($vendorIds, $shop_disable);
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) have been updated.', count($vendorIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __(' %1 An error occurred while updating the vendor(s) status.', $e->getMessage())
                );
            }
        }
        $this->_redirect('*/*/index');
    }


    /**
     * Validate batch of vendors before theirs status will be set
     * @param array $vendorIds
     * @param $status
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $vendorIds, $status)
    {
        $model = $this->vendorFactory->create();
        if ($status == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
            if (!$model->validateMassAttribute('shop_url', $vendorIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Some of the processed vendors have no Shop URL value defined. Please fill it prior to performing operations on these vendors.')
                );
            }
        }
        return $model;
    }

}
