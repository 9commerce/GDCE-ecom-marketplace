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

namespace Ced\CsMarketplace\Plugin;

use Magento\CatalogInventory\Model\Configuration as MagentoConfiguration;
use Magento\Framework\App\Request\Http;

/**
 * Class Configuration
 * @package Ced\CsMarketplace\Plugin
 */
class Configuration
{

	const ROUTE_NAME ='csmarketplace';


    /**
     * @var Http
     */
    public $request;


    /**
     * Configuration constructor.
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
       $this->request = $request;
    }

    /**
     * @param MagentoConfiguration $subject
     * @param $result
     * @param $store
     * @return boolean
     */
    public function afterIsShowOutOfStock(
    	MagentoConfiguration $subject,
    	$result,
    	$store = null
    	)
    {
        $route = $this->request->getRouteName();
        if($route==self::ROUTE_NAME)
        	return false;

        return $result;
    }
}
