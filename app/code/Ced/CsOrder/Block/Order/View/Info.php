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

use Magento\Sales\Model\Order\Address;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    /**
     * Customer service
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * Group service
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Metadata element factory
     * @var \Magento\Customer\Model\Metadata\ElementFactory
     */
    protected $_metadataElementFactory;

    /**
     * @var Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Ced\CsOrder\Model\Vorders
     */
    protected $vOrders;

    /**
     * Info constructor.
     * @param \Ced\CsOrder\Model\Vorders $vOrders
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param Address\Renderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        \Ced\CsOrder\Model\Vorders $vOrders,
        \Magento\Sales\Model\Order $order,
        \Magento\Customer\Model\SessionFactory $session,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        Address\Renderer $addressRenderer,
        array $data = []
    ) {
        $this->vOrders = $vOrders;
        $this->_order = $order;
        $this->_sessionFactory = $session;
        $this->groupRepository = $groupRepository;
        $this->metadata = $metadata;
        $this->_metadataElementFactory = $elementFactory;
        $this->addressRenderer = $addressRenderer;
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $groupRepository,
            $metadata,
            $elementFactory,
            $addressRenderer,
            $data
        );
    }

    /**
     * Get URL to edit the customer.
     * @return string
     */
    public function getCustomerViewUrl()
    {
        return '';
    }

    /**
     * Get order view URL.
     * @param  int $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        $session = $this->_sessionFactory->create();
        $vorder = $this->vOrders->loadByColumns(
            [
                'vendor_id' => trim($session->getVendorId()),
                'real_order_id' => trim($orderId)
            ]
        ) ;

        if ($vorder && $vorder->getId()) {
            return $this->getUrl(
                'csorder/vorders/view',
                [
                    'order_id' => $orderId,
                    'vorder_id' => $vorder->getId()
                ]
            );
        } else {
            return $this->getUrl('csorder/vorders/index');
        }
    }

    /**
     * Get link to edit order address page
     * @param Address $address
     * @param string $label
     * @return string
     */
    public function getAddressEditLink($address, $label = '')
    {
        return '';
    }

    /**
     * @return boolean
     */
    public function canShowCustomerDetails(){

        return $this->_scopeConfig->getValue('ced_csmarketplace/general/hide_customer_to_vendor',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
