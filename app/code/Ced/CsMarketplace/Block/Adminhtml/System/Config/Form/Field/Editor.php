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

namespace Ced\CsMarketplace\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Editor
 * @package Ced\CsMarketplace\Block\Adminhtml\System\Config\Form\Field
 */
class Editor extends Field
{

    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Editor constructor.
     * @param Context $context
     * @param Config $wysiwygConfig
     */
    public function __construct(
        Context $context,
        Config $wysiwygConfig
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     * @return mixed
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $config = $this->_wysiwygConfig->getConfig();
        $element->setWysiwyg(true);
        $element->setConfig($config);
        return parent::_getElementHtml($element);
    }
}
