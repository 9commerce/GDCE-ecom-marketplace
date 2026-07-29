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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Column\Renderer;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Orderdesc
 * @package Ced\CsMarketplace\Ui\Column\Renderer
 */
class Orderdesc extends Column
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * Orderdesc constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        OrderFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        $currency = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        $currency = $this->_localeCurrency->getCurrency($currency)->getSymbol();
        foreach ($dataSource['data']['items'] as $key => $item) {
            $amountDesc = $this->jsonSerializer->unserialize($item['amount_desc'], true);
            $html = '';
            foreach ($amountDesc as $incrementId => $baseNetAmount) {

                $orderInfo = $this->orderFactory->create()->loadByIncrementId($incrementId);
                $baseNetAmount = $orderInfo->getBaseSubtotalInclTax();

                $html .= '<label for="order_id_' . $incrementId . '"><b>' . __('Order') . '# </b>' . $incrementId .
                    '</label>, <br> <b>' . __('Amount') . ' </b>' . $currency . number_format($baseNetAmount, 2) . '<br/>';
                $dataSource['data']['items'][$key]['amount_desc'] = $html;
            }
        }
        return $dataSource;
    }
}
