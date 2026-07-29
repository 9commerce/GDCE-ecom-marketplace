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

class Items extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Disable submit button
     * @var bool
     */
    protected $_disableSubmitButton = false;

    /**
     * Sales data
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Items constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Customer\Model\SessionFactory $customerSession,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        $this->customerSession = $customerSession;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Prepare child blocks
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('invoice_item_container'),'" . $this->getUpdateUrl() . "')";
        $this->addChild(
            'update_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['class' => 'update-button', 'label' => __('Update Qty\'s'), 'onclick' => $onclick]
        );
        $this->_disableSubmitButton = true;
        $submitButtonClass = ' disabled';
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getQty()) {
                $this->_disableSubmitButton = false;
                $submitButtonClass = '';
                break;
            }
        }
        if ($this->getOrder()->getForcedShipmentWithInvoice()) {
            $_submitLabel = __('Submit Invoice and Shipment');
        } else {
            $_submitLabel = __('Submit Invoice');
        }
        $this->addChild(
            'submit_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => $_submitLabel,
                'class' => 'save submit-button primary' . $submitButtonClass,
                'onclick' => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
                'disabled' => $this->_disableSubmitButton
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve invoice order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getInvoice()->getOrder();
    }

    /**
     * Get is submit button disabled or not
     * @return bool
     */
    public function getDisableSubmitButton()
    {
        return $this->_disableSubmitButton;
    }

    /**
     * Retrieve invoice model instance
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve source
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Retrieve order totals block settings
     * @return array
     */
    public function getOrderTotalData()
    {
        return [];
    }

    /**
     * Format price
     * @param  float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getInvoice()->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve order totalbar block data
     * @return array
     */
    public function getOrderTotalbarData()
    {
        $this->setPriceDataObject($this->getInvoice()->getOrder());

        $totalbarData = [];
        $totalbarData[] = [__('Paid Amount'), $this->displayPriceAttribute('amount_paid'), false];
        $totalbarData[] = [__('Refund Amount'), $this->displayPriceAttribute('amount_refunded'), false];
        $totalbarData[] = [__('Shipping Amount'), $this->displayPriceAttribute('shipping_captured'), false];
        $totalbarData[] = [__('Shipping Refund'), $this->displayPriceAttribute('shipping_refunded'), false];
        $totalbarData[] = [__('Order Grand Total'), $this->displayPriceAttribute('grand_total'), true];
        return $totalbarData;
    }

    /**
     * Get update url
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('csorder/*/updateQty', ['order_id' => $this->getInvoice()->getOrderId()]);
    }

    /**
     * Get update button html
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }

    /**
     * Check shipment availability for current invoice
     * @return bool
     */
    public function canCreateShipment()
    {
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->getQtyToShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if capture operation is allowed in ACL
     * @return bool
     */
    public function isCaptureAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::capture');
    }

    /**
     * Check if qty can be edited
     * @return bool
     */
    public function canEditQty()
    {
        if ($this->getInvoice()->getOrder()->getPayment()->canCapture()) {
            return $this->getInvoice()->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }

    /**
     * Check if invoice can be captured
     * @return bool
     */
    public function canCapture()
    {
        return $this->getInvoice()->canCapture();
    }

    /**
     * Check if new invoice emails can be sent
     * @return bool
     */
    public function canSendInvoiceEmail()
    {
        return $this->_salesData->canSendNewInvoiceEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Check if gateway is associated with invoice order
     * @return bool
     */
    public function isGatewayUsed()
    {
        return $this->getInvoice()->getOrder()->getPayment()->getMethodInstance()->isGateway();
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->customerSession->create();
    }
}
