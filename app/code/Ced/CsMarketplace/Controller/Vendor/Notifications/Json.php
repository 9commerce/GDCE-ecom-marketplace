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

namespace Ced\CsMarketplace\Controller\Vendor\Notifications;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Json
 * @package Ced\CsMarketplace\Controller\Vendor\Notifications
 */
class Json extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Notification\CollectionFactory
     */
    public $notificationCollection;

    /**
     * Json constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Model\ResourceModel\Notification\CollectionFactory $notificationCollection
     */
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
        \Ced\CsMarketplace\Model\ResourceModel\Notification\CollectionFactory $notificationCollection
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->notificationCollection = $notificationCollection;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {

        } else {
            $size = $this->getRequest()->getParam('size');
            $page = $this->getRequest()->getParam('page');
            $notifications = $this->notificationCollection->create()
                ->addFieldToFilter('vendor_id', $this->_getSession()->getVendorId())
                ->setPageSize($size)
                ->setCurPage($page)
                ->setOrder('id', 'DESC')
                ->toArray();
            $resultJson->setData($notifications);
        }
        return $resultJson;
    }
}
