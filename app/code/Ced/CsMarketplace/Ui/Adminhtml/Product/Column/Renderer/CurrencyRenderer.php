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

namespace Ced\CsMarketplace\Ui\Adminhtml\Product\Column\Renderer;

use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var Currency
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
        \Magento\Framework\Locale\Currency $localeCurrency,
        StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->storeConfig = $storeConfig;
        $this->_localeCurrency = $localeCurrency;
        $this->currencyCode = $currencyFactory->create();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as $key => $item)
        {
            $dataSource['data']['items'][$key]['price'] = $this->getToCurrency($item['price']);
        }
        return $dataSource;
    }

    /**
     * @param $amt
     * @return string
     * @throws NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function getToCurrency($amt)
    {
        return $this->_localeCurrency->getCurrency(
            $this->storeConfig->getStore(null)->getBaseCurrencyCode()
        )->toCurrency($amt);
    }
}
