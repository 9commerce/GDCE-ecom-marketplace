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

use Magento\Backend\App\Action\Context;

/**
 * Class Massremove
 * @package Ced\CsProAssign\Controller\Adminhtml\Assign
 */
class Massremove extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $collection;

    /**
     * Massremove constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $collectionFactory,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->collection = $collectionFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $enable = $this->_scopeConfig->getValue(
            'ced_csmarketplace/general/csproassignactivation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        if ($enable) {
            $ids = $this->getRequest()->getParam('entity_id');
            if (!is_array($ids) || empty($ids)) {
                $this->messageManager->addErrorMessage(__('Please select product(s).'));
            } else {
                $vproductModel = $this->collection->create()
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->addFieldToFilter('product_id', ['in' => $ids])->walk('delete');
                $this->messageManager->addSuccessMessage(__("Product(s) was successfully removed from vendor"));
            }
        }
        $this->_redirect('csmarketplace/vendor/edit/vendor_id/' . $vendor_id);
    }
}


