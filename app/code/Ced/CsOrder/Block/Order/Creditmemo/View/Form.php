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

namespace Ced\CsOrder\Block\Order\Creditmemo\View;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Form
{
    /**
     * @var \Ced\CsOrder\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Ced\CsOrder\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\CsOrder\Helper\Data
     */
    protected $csorderHelper;

    /**
     * @var null
     */
    private $_csOrderCollection = null;

    /**
     * @var null
     */
    private $_csOrder = null;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Ced\CsOrder\Helper\Data $csorderHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsOrder\Model\Vorders $vorders
     * @param \Ced\CsOrder\Model\VordersFactory $vordersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsOrder\Model\Vorders $vorders,
        \Ced\CsOrder\Model\VordersFactory $vordersFactory,
        array $data = []
    ) {
        $this->csorderHelper = $csorderHelper;
        $this->customerSession = $customerSession;
        $this->vorders = $vorders;
        $this->vordersFactory = $vordersFactory;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Get order url
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('csorder/vorders/view', ['order_id' => $this->getCreditmemo()->getOrderId()]);
    }

    /**
     * @return \Ced\CsOrder\Helper\Data
     */
    public function getCsorderHelper()
    {
        return $this->csorderHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getVendorOrders()
    {
        if ($this->_csOrderCollection === null) {
            $this->_csOrderCollection = $this->vorders->getCollection();
        }
        return $this->_csOrderCollection;
    }

    /**
     * @return \Ced\CsOrder\Model\Vorders|null
     */
    public function getVendorOrder()
    {
        if ($this->_csOrder === null) {
            $vorder = $this->vordersFactory->create();
            $vendorId = $this->customerSession->getVendorId();
            $orderId= $this->getCreditmemo()->getOrder()->getIncrementId();
            $attributes = ['order_id'=> $orderId, 'vendor_id'=> $vendorId];
            $this->_csOrder = $vorder->loadByColumns($attributes);
        }
        return $this->_csOrder;
    }
}
