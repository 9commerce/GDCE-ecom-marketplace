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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadLayout implements ObserverInterface
{
    /**
     * @var \Ced\CsProduct\Helper\Data
     */
    protected $helper;

    /**
     * LoadLayout constructor.
     * @param \Ced\CsProduct\Helper\Data $helper
     */
    public function __construct(
        \Ced\CsProduct\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->helper->cleanCache();
        return $this;
    }
}
