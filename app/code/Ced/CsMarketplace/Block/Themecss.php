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
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Themecss
 * @package Ced\CsMarketplace\Block
 */
class Themecss extends Template
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $themeColor = $this->_scopeConfig->getValue(
            'ced_csmarketplace/general/theme_color',
            ScopeInterface::SCOPE_STORE
        );
        $this->pageConfig->addPageAsset('css/seller-reg.css');
        $this->pageConfig->addPageAsset('css/color/' . $themeColor);
    }
}
