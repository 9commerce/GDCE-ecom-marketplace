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

namespace Ced\CsOrder\Ui\Component\Listing\Columns\Vinvoice;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class GrandTotal extends Column
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoice;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice
     */
    protected $_invoiceResource;

    /**
     * @var \Ced\CsOrder\Model\InvoiceGrid
     */
    protected $invoiceGridFactory;

    /**
     * GrandTotal constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource
     * @param \Ced\CsOrder\Model\InvoiceGridFactory $invoiceGridFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource,
        \Ced\CsOrder\Model\InvoiceGridFactory $invoiceGridFactory,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->invoice = $invoice;
        $this->_invoiceResource = $invoiceResource;
        $this->invoiceGridFactory = $invoiceGridFactory;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendorId = $this->customerSession->getVendorId();
                $invoice = $this->invoice;
                $this->_invoiceResource->load($invoice, $item['invoice_id']);
                $invoice = $this->invoiceGridFactory->create()->setVendorId($vendorId)->updateTotal($invoice);

                $item['grand_total'] = $this->priceCurrency->format($invoice->getBaseGrandTotal(),false,2,null,$invoice->getBaseCurrencyCode());
            }
        }
        return $dataSource;
    }
}
