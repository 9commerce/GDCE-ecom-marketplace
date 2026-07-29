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

namespace Ced\CsMarketplace\Block\Vendor;


use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Ced\CsMarketplace\Block\Vendor\AbstractBlock;

/**
 * Class Notifications
 * @package Ced\CsMarketplace\Block\Vendor
 */
class Notifications extends AbstractBlock
{

    /**
     * @var \Ced\CsMarketplace\Model\NotificationHandler
     */
    protected $_notificationHandler;

    /**
     * Notifications constructor.
     * @param \Ced\CsMarketplace\Model\NotificationHandler $notificationHandler
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Model\NotificationHandler $notificationHandler,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    ) {
        $this->_notificationHandler = $notificationHandler;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->_notificationHandler->getNotifications();
    }
}
