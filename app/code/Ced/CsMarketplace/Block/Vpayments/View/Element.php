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

namespace Ced\CsMarketplace\Block\Vpayments\View;

use Magento\Framework\Data\Form\Element\AbstractElement;


/**
 * Class Element
 * @package Ced\CsMarketplace\Block\Vpayments\View
 */
class Element extends \Ced\CsMarketplace\Block\Widget\Form\Renderer\Fieldset\Element
{

    /**
     * @var
     */
    protected $_element;

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Ced_CsMarketplace::vpayments/view/element.phtml');
    }
}
