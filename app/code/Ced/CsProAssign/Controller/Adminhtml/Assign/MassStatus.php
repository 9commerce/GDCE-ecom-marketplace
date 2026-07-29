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
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsProAssign\Controller\Adminhtml\Assign;

use Magento\Backend\App\Action;

/**
 * Class MassStatus
 * @package Ced\CsProAssign\Controller\Adminhtml\Assign
 */
class MassStatus extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * MassStatus constructor.
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param Action\Context $context
     */
    public function __construct(\Ced\CsMarketplace\Model\Vproducts $vproducts, Action\Context $context)
    {
        $this->vproducts = $vproducts;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $checkstatus = $this->getRequest()->getParam('status');
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        $productIds = $this->getRequest()->getParam('entity_id');

        if (!is_array($productIds)) {
            $this->messageManager->addErrorMessage(__('Please select products(s).'));
        } elseif (!empty($productIds) && $checkstatus != '') {
            try {
                $errors = $this->vproducts->changeVproductStatus($productIds, $checkstatus);
                if ($errors['success']) {
                    $this->messageManager->addSuccessMessage(__("Status changed Successfully"));
                }
                if ($errors['error']) {
                    $this->messageManager
                        ->addErrorMessage(__('Can\'t process approval/disapproval for some products.Some of Product\'s
                        vendor(s) are disapproved or not exist.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('%1', $e->getMessage()));
            }
        }
        return $this->_redirect('csmarketplace/vendor/edit/vendor_id/' . $vendor_id);
    }
}
