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

namespace Ced\CsMarketplace\Controller\Vreports;
/**
 * Class Index
 * @package Ced\CsMarketplace\Controller\Vreports
 */
class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @return bool|\Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Vendor Reports'));
        return $resultPage;
    }
}
