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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model;

/**
 * Class NotificationHandler
 * @package Ced\CsMarketplace\Model
 */
class NotificationHandler
{

    /**
     * @var array $params
     */
    private $notificationList;

    /**
     * @param array $notificationList
     */
    public function __construct(
        array $notificationList = []
    ) {

        $this->notificationList = $notificationList;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        $notifications = [];

        foreach ($this->notificationList as $notification) {
            $notifications = array_merge($notification->getNotifications(), $notifications);
        }
        return $notifications;
    }
}
