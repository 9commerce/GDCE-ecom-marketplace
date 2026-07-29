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

namespace Ced\CsOrder\Ui\Component\Listing\Columns\Vcreditmemo;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
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
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditMemo;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo
     */
    protected $creditMemoResource;

    /**
     * @var \Ced\CsOrder\Model\CreditmemoGrid
     */
    protected $creditMemoGrid;

    /**
     * GrandTotal constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Creditmemo $creditMemo
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo $creditMemoResource
     * @param \Ced\CsOrder\Model\CreditmemoGrid $creditMemoGrid
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Creditmemo $creditMemo,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo $creditMemoResource,
        \Ced\CsOrder\Model\CreditmemoGrid $creditMemoGrid,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->creditMemo = $creditMemo;
        $this->creditMemoResource = $creditMemoResource;
        $this->creditMemoGrid = $creditMemoGrid;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendorId = $this->customerSession->getVendorId();
                $Creditmemo = $this->creditMemo;
                $this->creditMemoResource->load($Creditmemo, $item['creditmemo_id']);
                $Creditmemo = $this->creditMemoGrid->setVendorId($vendorId)->updateTotal($Creditmemo);
                $item['base_grand_total']= $this->priceCurrency->format(
                    $Creditmemo->getBaseGrandTotal(),
                    false,
                    2,
                    null,
                    $Creditmemo->getBaseCurrencyCode()
                );
            }
        }
        return $dataSource;
    }
}
