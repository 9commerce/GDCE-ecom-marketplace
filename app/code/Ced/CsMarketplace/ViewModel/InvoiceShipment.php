<?php

namespace Ced\CsMarketplace\ViewModel;

class InvoiceShipment implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Ced\CsMarketplace\Helper\InvoiceShipment
     */
    protected $invoiceShipment;

    /**
     * @param \Ced\CsMarketplace\Helper\InvoiceShipment $invoiceShipment
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\InvoiceShipment $invoiceShipment
    ) {
        $this->invoiceShipment = $invoiceShipment;
    }

    /**
     * @return mixed
     */
    public function isModuleEnable()
    {
        return $this->invoiceShipment->isModuleEnable();
    }

    /**
     * @return mixed
     */
    public function canSeparateInvoiceAndShipment()
    {
        return $this->invoiceShipment->canSeparateInvoiceAndShipment();
    }

}
