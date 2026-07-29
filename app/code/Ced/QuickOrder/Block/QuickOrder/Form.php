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
 * @package     Ced_QuickOrder
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\QuickOrder\Block\QuickOrder;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;



class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * Form constructor.
     * @param Context $context
     * @param UrlInterface $urlInterface
     * @param array $data
     */
	public function __construct(
        Context $context,
		UrlInterface $urlInterface,
		array $data = []
	) {
        $this->_urlInterface = $urlInterface;
		parent::__construct($context, $data);

	}

    /**
     * @return baseUrl
     */
	public function getBaseUrl(){
        $currentStore = $this->_storeManager->getStore();
        $baseUrl  = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
        return $baseUrl;
    }	

    public function getUrlInterface(){
        return $this->_urlInterface;
    }
}

