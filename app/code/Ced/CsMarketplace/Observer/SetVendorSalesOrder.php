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


use Ced\CsMarketplace\Model\SetVendorOrderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetVendorSalesOrder
 * @package Ced\CsMarketplace\Observer
 */
Class SetVendorSalesOrder implements ObserverInterface
{

    /**
     * @var SetVendorOrderFactory
     */
    protected $setVendorOrderFactory;

    /**
     * SetVendorSalesOrder constructor.
     * @param SetVendorOrderFactory $setVendorOrderFactory
     */
    public function __construct(SetVendorOrderFactory $setVendorOrderFactory)
    {
        $this->setVendorOrderFactory = $setVendorOrderFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->setVendorOrderFactory->create()->setVendorOrder($order);
    }
}
