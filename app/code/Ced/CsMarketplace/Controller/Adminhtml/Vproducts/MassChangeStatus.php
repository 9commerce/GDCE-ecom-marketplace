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
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassStatus
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vproducts
 */
class MassChangeStatus extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * MassStatus constructor.
     * @param Action\Context $context
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct($context);
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $checkstatus = $this->getRequest()->getParam('status');
        $productIds = $this->getRequest()->getParam('selected') ? $this->getRequest()->getParam('selected') : $this
            ->getRequest()->getParam('entity_id');
        if (!is_array($productIds)) {
            $this->messageManager->addErrorMessage(__('Please select products(s).'));
        } elseif (!empty($productIds) && $checkstatus !== '') {
            try {
                $errors = $this->vproductsFactory->create()->changeVproductStatus($productIds, $checkstatus);
                if ($errors['error']) {
                    $this->messageManager->addErrorMessage(__('Can\'t process approval/disapproval for some products.Some of Product\'s vendor(s) are disapproved or not exist.'));
                }
                if ($errors['success']) {
                    $this->messageManager->addSuccessMessage(__("Status changed Successfully"));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('%1', $e->getMessage()));
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('csmarketplace/vendor/edit', ['vendor_id'=>$this->getRequest()->getParam('vendor_id')]);
    }
}
