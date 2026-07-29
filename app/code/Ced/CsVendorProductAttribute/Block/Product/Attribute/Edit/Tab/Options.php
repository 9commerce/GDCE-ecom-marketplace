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
  * @category  Ced
  * @package   Ced_CsVendorProductAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsProduct\Block\Product\Attribute\Edit\Tab;

/**
 * Class Options
 * @package Ced\CsProduct\Block\Product\Attribute\Edit\Tab
 */
class Options extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\AbstractOptions
{

    /**
     * @return mixed
     */
    protected function _prepareLayout()
    {
        $this->addChild('labels', 'Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Labels');
        $this->addChild('options', 'Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options');
        return parent::_prepareLayout();
    }
}
