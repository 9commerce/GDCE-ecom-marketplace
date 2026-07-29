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

namespace Ced\CsMarketplace\Controller\Account;

use Ced\CsMarketplace\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class ApprovalPost
 * @package Ced\CsMarketplace\Controller\Account
 */
class ApprovalPost extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    public $_vendor;

    /**
     * ApprovalPost constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param Data $helperdata
     * @param \Ced\CsMarketplace\Model\VendorFactory $Vendor
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        Data $helperdata,
        \Ced\CsMarketplace\Model\VendorFactory $Vendor
    ) {
        $this->_vendor = $Vendor->create();
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $resultJsonFactory,
            $helperdata,
            $aclHelper,
            $Vendor
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('is_vendor') == 1) {
            $venderData = $this->getRequest()->getParam('vendor');
            $customerData = $this->_getSession()->getCustomer();
            $venderData['shop_url'] = strtolower($venderData['shop_url']);
            try {
                $vData = $this->_vendor->getCollection()->addAttributeToFilter('shop_url', $venderData['shop_url'])
                    ->getData();
                if (count($vData) > 0) {
                    $this->messageManager->addErrorMessage(__('Shop url already exist. Please Provide another Shop Url'));
                    return $resultRedirect->setPath('csmarketplace/vendor/index');
                }
                $vendor = $this->_vendor->setCustomer($customerData)->register($venderData);

                if (!$vendor->getErrors()) {

                    $vendor->save();
                    if ($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_NEW_STATUS) {
                        $this->messageManager->addSuccessMessage(__('Your vendor application has been Pending.'));
                        $resultRedirect->setPath('csmarketplace/vendor/index');
                    } else if ($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
                        $this->messageManager->addSuccessMessage(__('Your vendor application has been Approved.'));
                        $resultRedirect->setPath('csmarketplace/vendor/index');
                    }
                } elseif ($vendor->getErrors()) {
                    foreach ($vendor->getErrors() as $error) {
                        $this->messageManager->addErrorMessage($error);
                    }
                    $this->_getSession()->setFormData($venderData);
                } else {
                    $this->messageManager->addErrorMessage(__('Your vendor application has been denied'));
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect;
    }
}
