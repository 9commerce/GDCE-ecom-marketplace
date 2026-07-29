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

namespace Ced\CsMarketplace\Controller\Vendor;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Tool\Image;
use Ced\CsMarketplace\Model\NotificationHandler;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Navigation
 * @package Ced\CsMarketplace\Controller\Vendor
 */
class Navigation extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var UrlFactory
     */
    public $urlFactory;

    /**
     * @var Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var NotificationHandler
     */
    public $notificationHandler;

    /**
     * @var VendorFactory
     */
    public $vendor;

    /**
     * @var Image
     */
    public $imageHelper;

    /**
     * @var LayoutInterface
     */
    public $layout;

    /**
     * Navigation constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param Data $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param NotificationHandler $notificationHandler
     * @param Image $imageHelper
     * @param LayoutInterface $layoutInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        NotificationHandler $notificationHandler,
        Image $imageHelper,
        LayoutInterface $layoutInterface
    ) {
        $this->urlFactory = $urlFactory;
        $this->resultJsonFactory = $jsonFactory;
        $this->notificationHandler = $notificationHandler;
        $this->vendor = $vendor;
        $this->imageHelper = $imageHelper;
        $this->layout = $layoutInterface;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Default vendor dashboard page
     *
     * @return Json
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = [];
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {

        } else {
            $result['notifications'] = $this->notificationHandler->getNotifications();
            $vendorId = $this->_getSession()->getVendorId();
            $vendor = $this->vendor->create()->load($vendorId);
            $helper = $this->imageHelper;
            $block = $this->layout->createBlock('Ced\CsMarketplace\Block\Vendor\Navigation\Statatics',
                'csmarketplace_vendor_navigation_statatics_header');
            $block->getVendorAttributeInfo();

            $percent = round(($block->getSavedAttr() * 100)/$block->getTotalAttr());
            $href = '#';
            $urlFactory = $this->urlFactory->create();
            if($vendorId && $vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS ){
                $href = $urlFactory->getUrl('csmarketplace/vendor/profile',['_secure' => true]);
            }
            $result['statistics']['percent'] = $percent ;
            $result['statistics']['href'] = $href;
            $result['vendor']['profile_pic'] = $helper->getResizeImage($vendor->getData('profile_picture'),
                'logo', 50, 50);
            $result['vendor']['is_approved'] = 0;
            $result['vendor']['name'] = $vendor->getName();
            if($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
                $result['vendor']['is_approved'] = 1;
                $result['vendor']['status'] = __('Approved');
                $result['vendor']['status_itag'] = 'fa fa-circle text-success';

            } elseif($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_DISAPPROVED_STATUS) {
                $result['vendor']['status'] = __('Disapproved');
                $result['vendor']['status_itag'] = 'fa fa-circle text-danger';
            } else {
                $result['vendor']['status'] = __('New');;
                $result['vendor']['status_itag'] = 'fa fa-circle text-warning';
            }
            $result['vendor']['profile_url'] = $urlFactory->getUrl('csmarketplace/vendor/profileview/',array('_secure'=>true));
            $result['vendor']['settings_url'] = $urlFactory->getUrl('csmarketplace/vsettings/',array('_secure'=>true));
            $result['vendor']['logout_url'] = $urlFactory->getUrl('csmarketplace/account/logout/',array('_secure'=>true));

            $result['vendor']['shop_url'] = $vendor->getVendorShopUrl();
        }
        $resultJson->setData($result);
        return $resultJson;
     }
}
