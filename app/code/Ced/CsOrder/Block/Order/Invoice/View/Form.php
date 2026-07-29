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

namespace Ced\CsOrder\Block\Order\Invoice\View;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $session;

    /**
     * @var \Ced\CsOrder\Model\Vorders
     */
    public $vorders;

    /**
     * @var null
     */
    private $_csOrder = null;

    /**
     * Form constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsOrder\Model\Vorders $vorders
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Ced\CsOrder\Model\Vorders $vorders,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        array $data = []
    ) {
        $this->csorderHelper = $csorderHelper;
        $this->session = $session;
        $this->vorders = $vorders;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('csorder/vorders/view', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
    public function getCsorderHelper()
    {
        return $this->csorderHelper;
    }

    /**
     * @return \Ced\CsOrder\Model\Vorders
     */
    public function getVorder()
    {
        if ($this->_csOrder === null) {
            $vendorId = $this->session->getVendorId();
            $orderId = $this->getInvoice()->getOrder()->getIncrementId();
            $attributes = ['order_id' => $orderId, 'vendor_id' => $vendorId];
            $this->_csOrder = $this->vorders->loadByColumns($attributes);
        }
        return $this->_csOrder;
    }
}
