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
namespace Ced\CsMarketplace\Model\ResourceModel\Vshop;

/**
 * Class Flatcatalog
 * @package Ced\CsMarketplace\Model\ResourceModel\Vshop
 */
class Flatcatalog
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $http;
    
    /**
     * Flatcatalog constructor.
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
     * @param $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsEnabledFlat($subject,callable $proceed)
    {
        $module = $this->http->getModuleName();
        $controller = $this->http->getControllerName();

        if(($module == 'csproduct' && $controller == 'vproducts') || ($module == 'csmarketplace' && $controller == 'vproducts') ||
            ($module =='csmarketplace' && $controller == 'vshops')|| ($module == "cscmspage") || ($module == "cscategorymap")) {

            return false;
        }
        $returnValue = $proceed();
    }
}
