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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Message;

use Ced\CsMarketplace\Model\Vpayment\Requested;

/**
 * Class PendingPayments
 * @package Ced\CsMarketplace\Model\System\Message
 */
class PendingPayments implements \Magento\Framework\Notification\MessageInterface
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Requested
     */
    protected $requested;

    /**
     * PendingPayments constructor.
     * @param Requested $requested
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        Requested $requested,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->requested = $requested;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        // Retrieve unique message identity
        return sha1('PENDING_PAYMENTS');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        $collection = $this->requested->getCollection()
            ->addFieldToFilter('status', ['eq' => Requested::PAYMENT_STATUS_REQUESTED]);
        $collection->getSelect()->group("vendor_id");

        // Return true to show your message, false to hide it
        return (count($collection) > 0) ? true : false;
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_CRITICAL;
    }
    /**
     * @return string|void
     */
    public function getText()
    {
        // Retrieve message text
        /* return '<b>'.count($collection).'</b>'.__(' Vendor(s) have requested for Payment(s).'.__(' Release Payment(s) from').'<a href="'.$this->_urlBuilder->getUrl('csmarketplace/vpayments/index').'">'.__(' Requested Payments Panel').'</a>');*/
    }

}
