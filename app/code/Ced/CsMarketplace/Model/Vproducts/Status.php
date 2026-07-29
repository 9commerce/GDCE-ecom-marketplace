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

namespace Ced\CsMarketplace\Model\Vproducts;

/**
 * Class Status
 * @package Ced\CsMarketplace\Model\Vproducts
 */
class Status extends \Ced\CsMarketplace\Model\FlatAbstractModel
{

    /**
     * Initialize vproducts model
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vproducts\Status');
    }
}


