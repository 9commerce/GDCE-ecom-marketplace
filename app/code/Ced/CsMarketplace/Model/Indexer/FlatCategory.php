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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMarketplace\Model\Indexer;

/**
 * Class FlatCategory
 * @package Ced\CsMarketplace\Model\Indexer
 */
class FlatCategory extends \Magento\Catalog\Model\Indexer\Category\Flat\State
{

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Http
     */
    protected $http;

    /**
     * FlatCategory constructor.
     * @param \Magento\Framework\App\Request\Http $http
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $http,
        \Magento\Framework\App\State $state
    ){
        $this->state = $state;
        $this->http = $http;
    }

    /**
     * @return bool
     */
    public function isFlatEnabled()
    {
        $module = $this->http->getModuleName();
        $controller = $this->http->getControllerName();
        if(($this->state->getAreaCode()=='adminhtml' && $module == 'csmarketplace') || ($module == 'csproduct' && $controller == 'vproducts') || ($module == 'csmarketplace' && $controller == 'vproducts') ||
            ($module =='csmarketplace' && $controller == 'vshops')|| ($module == "cscmspage") || ($module == "cscategorymap")) {
            return false;
        }
    }

}
