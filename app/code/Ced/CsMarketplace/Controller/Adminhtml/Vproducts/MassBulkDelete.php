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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vproducts;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vproducts
 */
class MassBulkDelete extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    const XML_PATH_PRODUCT_EMAIL_IDENTITY = 'ced_vproducts/general/email_identity';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    public $mailHelper;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Helper\Mail $mailHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Helper\Mail $mailHelper
    ) {
        $this->productFactory = $productFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->mailHelper = $mailHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParams()['selected'];
        $checkstatus = \Ced\CsMarketplace\Model\Vproducts::DELETED_STATUS;
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $vendorIds = [];
                foreach ($ids as $id) {
                    $product = $this->productFactory->create()->load($id);
                    if ($product && $product->getId()) {
                        $v_id = $this->vproductsFactory->create()->getVendorIdByProduct($product->getId());
                        $vendorIds[$v_id][] = ["name" => $product->getName(), "sku" => $product->getSku()];
                        $product->delete();
                    }
                }
                $this->mailHelper->ProductDelete($checkstatus, $vendorIds);
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}

