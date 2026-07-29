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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Adminhtml\Payment\Column\Renderer;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;

/**
 * Class CurrencyRenderer
 * @package Ced\CsMarketplace\Ui\Adminhtml\Column\Renderer
 */
class CurrencyRenderer extends Column
{

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyCode;

    /**
     * @var StoreManagerInterface
     */
    protected $storeConfig;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $_localeCurrency;


    /**
     * CurrencyRenderer constructor.
     * @param StoreManagerInterface $storeConfig
     * @param CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\Currency $localeCurrency,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_localeCurrency = $localeCurrency;
        $this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as $key => $item)
        {
            $dataSource['data']['items'][$key]['amount'] = $this->getToCurrency($item['amount']);
            $dataSource['data']['items'][$key]['fee'] = $this->getToCurrency($item['fee']);
            $dataSource['data']['items'][$key]['net_amount'] = $this->getToCurrency($item['net_amount']);
        }
        return $dataSource;
    }

    /**
     * @param $amt
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function getToCurrency($amt)
    {
        return $this->_localeCurrency->getCurrency(
            $this->storeConfig->getStore(null)->getBaseCurrencyCode()
        )->toCurrency($amt);
    }
}
