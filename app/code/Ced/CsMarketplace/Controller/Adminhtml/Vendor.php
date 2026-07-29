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

namespace Ced\CsMarketplace\Controller\Adminhtml;

class Vendor extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {

        switch ($this->getRequest()->getControllerName()) {
            case 'vendor' :
                return $this->vendorAcl();
            case 'attributes' :
                return $this->attributesAcl();
            case 'vproducts' :
                return $this->vproductsAcl();
            case 'vendororder' :
                return $this->vendororderAcl();
            case 'vpayments' :
                return $this->vpaymentsAcl();
            default :
                return $this->_authorization->isAllowed('Ced_CsMarketplace::csmarketplace');
        }
    }

    /**
     * ACL check for VendorController.php
     *
     * @return bool
     */
    protected function vendorAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::manage_vendor');
        }
    }

    /**
     * ACL check for AttributesController.php
     *
     * @return bool
     */
    protected function attributesAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('csmarketplace/attributes');
        }
    }

    /**
     * ACL check for VproductsController.php
     *
     * @return bool
     */
    protected function vproductsAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            case 'index' :
                return $this->_authorization->isAllowed('Ced_CsMarketplace::vendorall_product');
            case 'pending' :
                return $this->_authorization->isAllowed('Ced_CsMarketplace::vendorapproved_product');
            case 'approved' :
                return $this->_authorization->isAllowed('Ced_CsMarketplace::vendorpending_product');
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::vendorall_product');
        }
    }

    /**
     * ACL check for VendororderController.php
     *
     * @return bool
     */
    protected function vendororderAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::managevendor_order');
        }
    }

    /**
     * ACL check for VpaymentsController.php
     *
     * @return bool
     */
    protected function vpaymentsAcl()
    {

        switch ($this->getRequest()->getActionName()) {
            default:
                return $this->_authorization->isAllowed('Ced_CsMarketplace::managevendor_transaction');
        }
    }
}
