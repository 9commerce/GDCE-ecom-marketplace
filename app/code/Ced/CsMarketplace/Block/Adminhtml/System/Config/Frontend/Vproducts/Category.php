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


namespace Ced\CsMarketplace\Block\Adminhtml\System\Config\Frontend\Vproducts;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Category
 * @package Ced\CsMarketplace\Block\Adminhtml\System\Config\Frontend\Vproducts
 */
class Category extends Field
{

    /**
     * Return element html
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->getLayout()
            ->createBlock(
                'Ced\CsMarketplace\Block\Adminhtml\System\Config\Frontend\Vproducts\Categories',
                'csmarketplace_system_config_categories'
            )->setElement($element)->toHtml();
    }
}
