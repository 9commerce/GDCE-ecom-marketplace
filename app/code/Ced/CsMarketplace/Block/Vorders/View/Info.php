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

namespace Ced\CsMarketplace\Block\Vorders\View;


use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Sales\Block\Order\Info as MagentoOrderInfo;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class Info
 * @package Ced\CsMarketplace\Block\Vorders\View
 */
class Info extends MagentoOrderInfo
{

    /**
     * Info constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $paymentHelper
     * @param Renderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $paymentHelper,
        Renderer $addressRenderer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('vorders/view/info.phtml');
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        $this->checkLinks();
        unset($this->_links['invoice']);
        return $this->_links;
    }

    /**
     * @return void
     */
    private function checkLinks()
    {
        $order = $this->getOrder();
        if (!$order->hasInvoices()) {
            unset($this->_links['invoice']);
        }
        if (!$order->hasShipments()) {
            unset($this->_links['shipment']);
        }
        if (!$order->hasCreditmemos()) {
            unset($this->_links['creditmemo']);
        }
    }

    /**
     * @return string|null
     */
    public function getOrderStoreName()
    {
        if ($this->getOrder()) {
            $storeId = $this->getOrder()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');
                return nl2br($this->getOrder()->getStoreName()) . $deleted;
            }

            $store = $this->_storeManager->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * @return boolean
     */
    public function canShowCustomerDetails(){

        return $this->scopeConfig->getValue('ced_csmarketplace/general/hide_customer_to_vendor',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
