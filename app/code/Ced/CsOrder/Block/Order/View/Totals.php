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

namespace Ced\CsOrder\Block\Order\View;

class Totals extends \Ced\CsMarketplace\Block\Vorders\View\Totals
{
    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsOrder\Helper\Data $csorderHelper
    ) {
        $this->csorderHelper = $csorderHelper;
        parent::__construct($context, $registry, $vordersFactory, $storeManager);
    }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
    public function getCsOrderHelper()
    {
        return $this->csorderHelper;
    }
}
