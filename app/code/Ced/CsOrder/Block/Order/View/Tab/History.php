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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Block\Order\View\Tab;

use Magento\Customer\Model\Session;

class History extends \Magento\Sales\Block\Adminhtml\Order\View\Tab\History
{
    /**
     * @var string
     */
    protected $_template = 'order/view/tab/history.phtml';

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param Session $customerSession
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        Session $customerSession,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->adminHelper = $adminHelper;
        $this->customerSession = $customerSession;
        $this->csorderHelper = $csorderHelper;
        $this->setData('area', 'adminhtml');
    }

    /**
     * Retrieve order model instance
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Status history item title getter
     * @param  array $item
     * @return string
     */
    public function getItemTitle(array $item)
    {
        return isset($item['title']) ? $this->escapeHtml($item['title']) : '';
    }

    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     * @return array
     * @throws \Exception
     */
    public function getFullHistory()
    {
        $order = $this->getOrder();

        $history = [];
        $valueIds = [0];
        $vendorId = $this->customerSession->getVendorId();
        $valueIds[] = $vendorId;
        $invoiceIds = $this->csorderHelper->getInvoiceIds($vendorId);
        $shipmentIds = $this->csorderHelper->getShipmentIds($vendorId);
        $creditmemoIds = $this->csorderHelper->getCreditMemoIds($vendorId);
        foreach ($order->getAllStatusHistory() as $orderComment) {
            if (!in_array(
                $orderComment->getVendorId(),
                $valueIds
            )) {
                continue;
            }
            $history[] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $this->getOrderAdminDate($orderComment->getCreatedAt()),
                $orderComment->getComment()
            );
        }
        foreach ($order->getCreditmemosCollection() as $_memo) {
            if (!in_array(
                $_memo->getEntityId(),
                $creditmemoIds
            )) {
                continue;
            }
            $history[] = $this->_prepareHistoryItem(
                __('Credit memo #%1 created', $_memo->getIncrementId()),
                $_memo->getEmailSent(),
                $this->getOrderAdminDate($_memo->getCreatedAt())
            );

            foreach ($_memo->getCommentsCollection() as $_comment) {
                if (!in_array(
                    $_comment->getVendorId(),
                    $valueIds
                )) {
                    continue;
                }
                $history[] = $this->_prepareHistoryItem(
                    __('Credit memo #%1 comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }
        foreach ($order->getShipmentsCollection() as $_shipment) {
            if (!in_array(
                $_shipment->getEntityId(),
                $shipmentIds
            )) {
                continue;
            }
            $history[] = $this->_prepareHistoryItem(
                __('Shipment #%1 created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent(),
                $this->getOrderAdminDate($_shipment->getCreatedAt())
            );

            foreach ($_shipment->getCommentsCollection() as $_comment) {
                if (!in_array(
                    $_comment->getVendorId(),
                    $valueIds
                )) {
                    continue;
                }
                $history[] = $this->_prepareHistoryItem(
                    __('Shipment #%1 comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }
        foreach ($order->getInvoiceCollection() as $_invoice) {
            if (!in_array(
                $_invoice->getEntityId(),
                $invoiceIds
            )) {
                continue;
            }
            $history[] = $this->_prepareHistoryItem(
                __('Invoice #%1 created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent(),
                $this->getOrderAdminDate($_invoice->getCreatedAt())
            );

            foreach ($_invoice->getCommentsCollection() as $_comment) {
                if (!in_array($_comment->getVendorId(), $valueIds)) {
                    continue;
                }
                $history[] = $this->_prepareHistoryItem(
                    __('Invoice #%1 comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment()
                );
            }
        }
        foreach ($order->getTracksCollection() as $_track) {
            if (!in_array(
                $_track->getParentId(),
                $shipmentIds
            )) {
                continue;
            }
            $history[] = $this->_prepareHistoryItem(
                __('Tracking number %1 for %2 assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $this->getOrderAdminDate($_track->getCreatedAt())
            );
        }

        usort($history, [__CLASS__, 'sortHistoryByTimestamp']);
        return $history;
    }

    /**
     * Status history date/datetime getter
     * @param array $item
     * @param string $dateType
     * @param int $format
     * @return string
     * @throws \Exception
     */
    public function getItemCreatedAt(array $item, $dateType = 'date', $format = \IntlDateFormatter::MEDIUM)
    {
        if (!isset($item['created_at'])) {
            return '';
        }
        $date = $item['created_at'] instanceof \DateTimeInterface
            ? $item['created_at']
            : new \DateTime($item['created_at']);
        if ('date' === $dateType) {
            return $this->_localeDate->formatDateTime($date, $format, $format);
        }
        return $this->_localeDate->formatDateTime($date, \IntlDateFormatter::NONE, $format);
    }

    /**
     * Status history item comment getter
     * @param  array $item
     * @return string
     */
    public function getItemComment(array $item)
    {
        $allowedTags = ['b', 'br', 'strong', 'i', 'u', 'a'];
        return isset($item['comment'])
            ? $this->adminHelper->escapeHtmlWithLinks($item['comment'], $allowedTags) : '';
    }

    /**
     * Check whether status history comment is with customer notification
     * @param  array $item
     * @param  bool  $isSimpleCheck
     * @return bool
     */
    public function isItemNotified(array $item, $isSimpleCheck = true)
    {
        if ($isSimpleCheck) {
            return !empty($item['notified']);
        }
        return isset($item['notified']) && false !== $item['notified'];
    }

    /**
     * Map history items as array
     * @param  string    $label
     * @param  bool      $notified
     * @param  \DateTimeInterface $created
     * @param  string    $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return ['title' => $label, 'notified' => $notified, 'comment' => $comment, 'created_at' => $created];
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Comments History');
    }

    /**
     * Get Class
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Class
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Get Tab Url
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('csorder/*/commentsHistory', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    //@codingStandardsIgnoreStart
    /**
     * Comparison For Sorting History By Timestamp
     * @param  mixed $a
     * @param  mixed $b
     * @return int
     */
    public static function sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        if ($createdAtA->getTimestamp() == $createdAtB->getTimestamp()) {
            return 0;
        }
        return $createdAtA->getTimestamp() < $createdAtB->getTimestamp() ? -1 : 1;
    }
    //@codingStandardsIgnoreEnd

    /**
     *  Get order admin date
     * @param int $createdAt
     * @return \DateTime
     * @throws \Exception
     */
    public function getOrderAdminDate($createdAt)
    {
        return $this->_localeDate->date(new \DateTime($createdAt));
    }

    /**
     * Customer Notification Applicable check method
     * @param  array $historyItem
     * @return bool
     */
    public function isCustomerNotificationNotApplicable($historyItem)
    {
        return $historyItem['notified'] ==
            \Magento\Sales\Model\Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }
}
