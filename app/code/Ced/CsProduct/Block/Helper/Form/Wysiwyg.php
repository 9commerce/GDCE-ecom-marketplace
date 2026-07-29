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

namespace Ced\CsProduct\Block\Helper\Form;

use Magento\Framework\Data\Form\Element\Textarea;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Module\Manager;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;

class Wysiwyg extends Textarea
{
    /**
     * Adminhtml data
     *
     * @var Data
     */
    protected $_backendData = null;

    /**
     * Catalog data
     *
     * @var Manager
     */
    protected $_moduleManager = null;

    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * Wysiwyg constructor.
     * @param Config $wysiwygConfig
     * @param LayoutInterface $layout
     * @param Manager $moduleManager
     * @param Data $backendData
     * @param Http $http
     * @param UrlInterface $url
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        LayoutInterface $layout,
        Manager $moduleManager,
        Data $backendData,
        Http $http,
        UrlInterface $url,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_layout = $layout;
        $this->_moduleManager = $moduleManager;
        $this->_backendData = $backendData;
        $this->http = $http;
        $this->url = $url;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Retrieve additional html and put it at the end of element html
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $module_name = $this->http->getModuleName();
        $url = $this->_backendData->getUrl('catalog/product/wysiwyg');
        if ($module_name == 'csproduct') {
            $url = $this->url->getUrl('csproduct/vproducts/wysiwyg');
        }

        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());

        $html = parent::getAfterElementHtml();
        if ($this->getIsWysiwygEnabled()) {
            $disabled = $this->getDisabled() || $this->getReadonly();
            $html .= $this->_layout->createBlock(
                \Magento\Backend\Block\Widget\Button::class,
                '',
                [
                    'data' => [
                        'label' => __('WYSIWYG Editor'),
                        'type' => 'button',
                        'disabled' => $disabled,
                        'class' => 'action-wysiwyg',
                        'onclick' => 'catalogWysiwygEditor.open(\'' . $url . '\', \'' . $this->getHtmlId() . '\')',
                    ]
                ]
            )->toHtml();
            $html .= <<<HTML
<script>
require([
    'jquery',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function(jQuery){

var config = $config,
    editor;

jQuery.extend(config, {
    settings: {
        theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
        theme_advanced_buttons2: null,
        theme_advanced_buttons3: null,
        theme_advanced_buttons4: null,
        theme_advanced_statusbar_location: null
    },
    files_browser_window_url: false
});

editor = new tinyMceWysiwygSetup(
    '{$this->getHtmlId()}',
    config
);

editor.turnOn();

jQuery('#{$this->getHtmlId()}')
    .addClass('wysiwyg-editor')
    .data(
        'wysiwygEditor',
        editor
    );
});
</script>
HTML;
        }
        return $html;
    }

    /**
     * Check whether wysiwyg enabled or not
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWysiwygEnabled()
    {
        if ($this->_moduleManager->isEnabled('Magento_Cms')) {
            return (bool)($this->_wysiwygConfig->isEnabled() && $this->getEntityAttribute()->getIsWysiwygEnabled());
        }

        return false;
    }
}
