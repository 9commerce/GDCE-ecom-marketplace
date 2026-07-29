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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

class Confirm extends \Ced\CsMarketplace\Controller\Vendor
{
    protected \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResource
    ) {
        $this->vendorResource = $vendorResource;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('vendor_id');
        if ($id) {
            $vendorData = $this->vendor->create();
            $this->vendorResource->load($vendorData, $id);
            if (!$vendorData->getIsVerify()){
                $token = str_replace(' ', '', $this->getRequest()->getParam('token'));
                if ($token === $vendorData->getVerifyKey()){
                    try {
                        $vendorData->setIsVerify(1);
                        $this->vendorResource->save($vendorData);
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Something went wrong while verifying the Vendor.'));
                        return $resultRedirect->setPath('csmarketplace/vendor/index');
                    }
                    $this->messageManager->addSuccessMessage(__('Your Vendor Account is Verified.'));
                    return $resultRedirect->setPath('csmarketplace/vendor/index');
                }
            }
        }
        $this->messageManager->addErrorMessage(__('Something went wrong.'));
        return $resultRedirect->setPath('csmarketplace/vendor/index');
    }
}
