<?php

namespace Ced\CsGst\Observer;

class ChangeConfiguration implements \Magento\Framework\Event\ObserverInterface
{

    protected $helper;
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $config,
        \Magento\Framework\App\RequestInterface $request
    ){
        $this->_request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $modulestatus = $this->config;

        $params =  $this->_request->getParam('groups');

        $enable = false;

        $text   = $this->scopeConfig->getValue('customer/address_templates/text');
        $online = $this->scopeConfig->getValue('customer/address_templates/oneline');
        $html   = $this->scopeConfig->getValue('customer/address_templates/html');
        $pdf    = $this->scopeConfig->getValue('customer/address_templates/pdf');
        $js     = $this->scopeConfig->getValue('customer/address_templates/js_template');
    }
}