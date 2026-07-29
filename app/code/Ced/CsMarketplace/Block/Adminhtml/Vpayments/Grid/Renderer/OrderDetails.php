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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer;


/**
 * Class OrderDetails
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer
 */
class OrderDetails extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var bool
     */
    protected $_frontend = false;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_currencyInterface;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * OrderDetails constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        array $data = []
    ) {
        $this->_currencyInterface = $localeCurrency;
        $this->design = $design;
        $this->orderFactory = $orderFactory;
        $this->vordersFactory = $vordersFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $amountDesc = $row->getAmountDesc();
        $html = '';
        $area = $this->design->getArea();
        if ($amountDesc != '') {
            $amountDesc = json_decode($amountDesc, true);
            foreach ($amountDesc as $incrementId => $baseNetAmount) {
                $amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
                $vorder = $this->orderFactory->create()->loadByIncrementId($incrementId);

                if ($area != 'adminhtml' && $vorder && $vorder->getId()) {
                    $html .= 'Order#' . $incrementId . ' Net Earned ' . $amount . ",";
                } else
                    $html .= 'Order#' . $incrementId . ' Amount' . $amount . ',';
            }
        }

        return $html;
    }

}