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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Block\Grouped\AssociatedProducts;

use Magento\GroupedProduct\Block\Product\Grouped\AssociatedProducts\ListAssociatedProducts
    as GroupedListAssociatedProducts;

class ListAssociatedProducts extends GroupedListAssociatedProducts
{
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }
}
