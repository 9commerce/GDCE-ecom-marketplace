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

namespace Ced\CsMarketplace\Observer;

use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetEmail
 * @package Ced\CsMarketplace\Observer
 */
Class SetEmail implements ObserverInterface
{

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * SetEmail constructor.
     * @param VendorFactory $vendorFactory
     */
    public function __construct(VendorFactory $vendorFactory)
    {
        $this->vendorFactory = $vendorFactory;
    }

    /**
     *Notify Customer Account share Change
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        $vendor = $this->vendorFactory->create()->loadByCustomerId($customer->getId());
        if ($vendor && $vendor->getEmail() != $customer->getEmail()) {
            $vendor->setSettingFromCustomer(true)->setEmail($customer->getEmail())->save();
        }
    }
}
