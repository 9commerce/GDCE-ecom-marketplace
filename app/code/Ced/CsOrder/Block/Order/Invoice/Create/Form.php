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

namespace Ced\CsOrder\Block\Order\Invoice\Create;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    public $csorderHelper;

    /**
     * Form constructor.
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->csorderHelper = $csorderHelper;
        $this->registry = $registry;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Get save url
     * @return string
     */
    public function getSaveUrl()
    {
        $vorder = $this->getRegistry()->registry('current_vorder');
        $order = $this->getRegistry()->registry('current_order');
        return $this->getUrl(
            'csorder/*/save',
            [
                'vorder_id' => $vorder->getId(),
                'order_id' => $order->getId(),
            ]
        );
    }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
    public function getcsorderHelper()
    {
        return $this->csorderHelper;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}
