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
 * Class MassDelete
 * @package Ced\CsProAssign\Controller\Adminhtml\Assign
 */
class MassDelete extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * MassDelete constructor.
     * @param \Magento\Catalog\Model\Product $product
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        Action\Context $context
    )
    {
        $this->product = $product;
        $this->vproducts = $vproducts;
        parent::__construct($context);
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('entity_id');
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select product(s).'));
        } else {
            try {
                $product = $this->product->getCollection()
                    ->addFieldToFilter('entity_id', ['in' => $ids])->walk('delete');

                $vproductModel = $this->vproducts->getCollection()
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->addFieldToFilter('product_id', ['in' => $ids])->walk('delete');

                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('csmarketplace/vendor/edit/vendor_id/' . $vendor_id);
    }
}
