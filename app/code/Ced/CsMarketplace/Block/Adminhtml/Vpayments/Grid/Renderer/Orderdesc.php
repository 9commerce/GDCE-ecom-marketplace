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
 * Class Orderdesc
 * @package Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer
 */
class Orderdesc extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_currencyInterface;

    /**
     * @var bool
     */
    protected $_frontend = false;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Orderdesc constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->vordersFactory = $vordersFactory;
        $this->_currencyInterface = $localeCurrency;
        $this->design = $design;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $amountDesc = $row->getAmountDesc();
        $area = $this->design->getArea();
        if ($amountDesc != '') {
            $amountDesc = json_decode($amountDesc, true);
            foreach ($amountDesc as $incrementId => $baseNetAmount) {
                $amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
                $vorder = $this->orderFactory->create()->loadByIncrementId($incrementId);

                $orderId = $this->vordersFactory->create()->load($incrementId, 'order_id')->getId();

                if ($area != 'adminhtml' && $vorder && $vorder->getId()) {
                    $url = $this->getUrl("csmarketplace/vorders/view/", array('order_id' => $orderId));
                    $target = "target='_blank'";
                    $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . "<a href='" . $url . "' " .
                        $target . " >" . $incrementId . "</a>" . '</label>, <b>Net Earned </b>' . $amount . '<br/>';
                } else
                    $html .= '<label for="order_id_' . $incrementId . '"><b>Order# </b>' . $incrementId .
                        '</label>, <b>Amount </b>' . $amount . '<br/>';
            }
        }

        return $html;
    }
}
