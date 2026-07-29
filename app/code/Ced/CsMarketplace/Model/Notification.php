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

namespace Ced\CsMarketplace\Model;

/**
 * Class Notification
 * @package Ced\CsMarketplace\Model
 */
class Notification extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var Vproducts
     */
    protected $vProducts;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceDataHelper;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var array $params
     */
    private $customerSession;

    /**
     * Notification constructor.
     * @param NotificationFactory $notificationFactory
     * @param Vproducts $vProducts
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Notification|null $resource
     * @param ResourceModel\Notification\Collection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\NotificationFactory $notificationFactory,
        \Ced\CsMarketplace\Model\Vproducts $vProducts,
        \Magento\Framework\View\LayoutInterface $layout,
        \Ced\CsMarketplace\Helper\Data $marketplaceDataHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?ResourceModel\Notification $resource = null,
        ?ResourceModel\Notification\Collection $resourceCollection = null,
        array $data = []
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->vProducts = $vProducts;
        $this->layout = $layout;
        $this->marketplaceDataHelper = $marketplaceDataHelper;
        $this->urlFactory = $urlFactory;
        $this->vendorFactory = $vendorFactory;
        $this->customerSession = $session;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Notification');
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        $vendorId = $this->customerSession->getVendorId();

        $notifications = [];
        if ($vendorId) {
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $urlFactory = $this->urlFactory->create();
            $block = $this->layout->createBlock('Ced\CsMarketplace\Block\Vendor\Navigation');
            $isFirst = !count($this->vProducts->getVendorProducts('', $vendorId));

            if ($vendor->getStatus() == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
                if (!$vendor->getProfilePicture()) {
                    $notifications[] = [
                        'url' => $urlFactory->getUrl('csmarketplace/vendor/profile', array('_secure' => true)),
                        'title' => __('Add Profile Picture'),
                        'itag' => 'icon-film icons',
                    ];
                }

                if (!$this->marketplaceDataHelper->isShopEnabled($vendor)) {
                    $notifications[] = [
                        'url' => '#',
                        'title' => __('Your Shop is disabled By Admin'),
                        'itag' => 'icon-bell icons',
                    ];
                }

                if (!$vendor->getCompanyLogo()) {
                    $notifications[] = [
                        'url' => $urlFactory->getUrl('csmarketplace/vendor/profile', array('_secure' => true)),
                        'title' => __('Add Company Logo'),
                        'itag' => 'icon-camera icons',
                    ];
                }

                if (!$vendor->getCompanyBanner()) {
                    $notifications[] = [
                        'url' => $urlFactory->getUrl('csmarketplace/vendor/profile', array('_secure' => true)),
                        'title' => __('Add Company Banner'),
                        'itag' => 'icon-bell icons',
                    ];
                }

                if ($isFirst) {
                    $notifications[] = [
                        'url' => $urlFactory->getUrl('csmarketplace/vproducts/new', array('_secure' => true)),
                        'title' => __('Add Your First Product'),
                        'itag' => 'icon-bell icons',
                    ];
                }

                if (!$block->isPaymentDetailAvailable()) {
                    $notifications[] = [
                        'url' => $urlFactory->getUrl('csmarketplace/vsettings/index', array('_secure' => true)),
                        'title' => __('Add your Payment Details'),
                        'itag' => 'icon-credit-card icons',
                    ];

                }
            }

            $notificationsCollection = $this->notificationFactory->create()->getCollection()
                ->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('status', 0)->setOrder('id', 'DESC');


            foreach ($notificationsCollection as $notification) {
                $notifications[] = [
                    'url' => $notification->getAction(),
                    'title' => __('%1', $notification->getTitle()),
                    'itag' => $notification->getItag(),
                    'created_at' => $notification->getCreatedAt()
                ];
            }
        }

        return $notifications;
    }
}
