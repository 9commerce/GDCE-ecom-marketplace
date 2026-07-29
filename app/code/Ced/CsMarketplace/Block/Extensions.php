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

namespace Ced\CsMarketplace\Block;

use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Feed;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Helper\Js;

/**
 * Class Extensions
 * @package Ced\CsMarketplace\Block
 */
class Extensions extends Fieldset
{

    const LICENSE_USE_HTTPS_PATH = 'web/secure/use_in_adminhtml';
    const LICENSE_VALIDATION_URL_PATH = 'system/license/license_url';
    const HASH_PATH_PREFIX = 'cedcore/extensions/extension_';

    /**
     * @var
     */
    protected $_dummyElement;

    /**
     * @var
     */
    protected $_fieldRenderer;

    /**
     * @var
     */
    protected $_values;

    /**
     * @var
     */
    protected $_licenseUrl;
    /**
     * helper
     *
     * @var Data
     */
    protected $_helper;

    /**
     * @var Feed
     */
    protected $feedHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var Field
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var Js
     */
    protected $_jsHelper;

    /**
     * Extensions constructor.
     * @param Field $field
     * @param Feed $feedHelper
     * @param Data $helper
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Field $field,
        Feed $feedHelper,
        Data $helper,
        Context $context,
        Session $authSession,
        Js $jsHelper,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_jsHelper = $jsHelper;
        $this->_authSession = $authSession;
        $this->_helper = $helper;
        $this->feedHelper = $feedHelper;
        $this->field = $field;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context, $authSession, $jsHelper);
        $this->_cacheManager = $this->_cache;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Exception
     */
    public function render(AbstractElement $element)
    {
        $html = $footer = $script = '';
        $header = $this->_getHeaderHtml($element);
        $modules = $this->feedHelper->getAllModules();

        $field = $element->addField('extensions_heading', 'note', array(
            'name' => 'extensions_heading',
            'label' => '<a href="javascript:;"><b>Extension Name (version)</b></a>',
            'text' => '<a href="javascript:;"><b>License Information</b></a>',
        ))->setRenderer($this->_getFieldRenderer());

        $html .= $field->toHtml();
        foreach ($modules as $moduleName => $releaseVersion) {
            $moduleProductName =
                isset($releaseVersion['parent_product_name']) ? $releaseVersion['parent_product_name'] : '';
            if (!is_array($releaseVersion))
                $releaseVersion = isset($releaseVersion['release_version']) ? $releaseVersion['release_version'] :
                    trim($releaseVersion);
            else
                $releaseVersion = isset($releaseVersion['release_version']) ? $releaseVersion['release_version'] : '';

            $html .= $this->_getFieldHtml($element, $moduleName, $releaseVersion, $moduleProductName);
        }

        if (strlen($html) == 0) {
            $html = '<p>' . $this->__('No records found.') . '</p>';
        }

        $footer .= $this->_getFooterHtml($element);
        $script .= $this->_getScriptHtml();

        return $header . $html . $footer . $script;
    }

    /**
     * @return Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->field;
        }
        return $this->_fieldRenderer;
    }

    /**
     * @param $fieldset
     * @param $moduleName
     * @param string $currentVersion
     * @param string $moduleProductName
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName, $currentVersion = '0.0.1', $moduleProductName = '')
    {
        // phpcs:disable Magento2.Files.LineLength.MaxExceeded
        $hash = self::HASH_PATH_PREFIX . strtolower($moduleName) . '_hash';
        $level = self::HASH_PATH_PREFIX . strtolower($moduleName) . '_level';

        $configData[$hash] = $this->_helper->getStoreConfig($hash);
        $configData[$level] = $this->_helper->getStoreConfig($level);

        $hash = isset($configData[$hash]) ? $configData[$hash] : '';
        $level = isset($configData[$level]) ? $configData[$level] : '';
        $l = $this->feedHelper->getLicenseFromHash($hash, $level);

        $path = self::HASH_PATH_PREFIX . strtolower($moduleName);
        $configData[$path] = $this->_helper->getStoreConfig($path);

        if (isset($configData[$path])) {
            $configData[$path] = $l;
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = '';
            $inherit = true;
        }

        $e = $this->_getDummyElement();

        $allExtensions = $this->_serializer->unserialize(
            $this->_cacheManager->load('all_extensions_by_cedcommerce')?:'{}'
        );
        $name = strlen($moduleProductName) > 0 ? $moduleProductName : $moduleName;
        $releaseVersion = $name . '-' . $currentVersion;
        $warning = '';
        if ($allExtensions && isset($allExtensions[$moduleName])) {
            $url = $allExtensions[$moduleName]['url'];
            $warning = isset($allExtensions[$moduleName]['warning']) ? $allExtensions[$moduleName]['warning'] : '';

            if (strlen($warning) == 0) {
                $releaseVersion = $allExtensions[$moduleName]['release_version'];
                $releaseVersion =
                    '<a href="' . $url . '" target="_blank" title="Upgarde Available(' . $releaseVersion . ')">' .
                    $name . '-' . $currentVersion . '</a>';
            } else {
                $releaseVersion =
                    '<div class="notification-global"><strong class="label">' . $warning . '</strong></div>';
            }
        }
        $buttonHtml =
            '<div style="float: right;"><div style="font-family:\'Admin Icons\';" class="message-success"></div></div>';
        $type = 'label';
        $title = 'License Number';
        if (strlen($data) == 0) {
            $title = __('Enter the valid license after that you have to click on Save Config button.');
            $text = __('Please fill the valid license number in above field. If you don\'t have license number please');
            $buttonHtml = '<div style="clear: both; height:0; width:0; ">&nbsp;</div>';
            $buttonHtml .= '<p class="note"><span>'.$text.' <a href="http://cedcommerce.com/licensing?product_name=' .
                strtolower($moduleName) .
                '" target="_blank">'.__("Get a license number from CedCommerce.com").'</a></span></p>';
            $type = 'text';
        }

        if ($moduleName && strtolower($moduleName) == 'ced_csvendorpanel') {
            $type = 'label';
            $path = self::HASH_PATH_PREFIX . 'ced_csmarketplace';
            if (isset($configData[$path])) {
                $data = $configData[$path];
                $inherit = false;
            } else {
                $data = (string)$this->getForm()->getConfigRoot()->descend($path);
                $inherit = true;
            }
            if (!$data) {
                $data = 'n/a';
            } else {
                $buttonHtml =
                    '<div style="float:right"><div style="font-family:\'Admin Icons\'" class="message-success" /></div>';
            }
        }
        //phpcs:enable

        $field = $fieldset->addField($moduleName, $type,
            //this is the type of the element (can be text, textarea, select, multiselect, ...)
            array(
                'name' => 'groups[extensions][fields][extension_' . strtolower($moduleName) . '][value]',
                //this is groups[group name][fields][field name][value]
                'label' => $name . ' (' . $currentVersion . ')',//this is the label of the element
                'value' => $data,//this is the current value
                'title' => $title,
                'inherit' => $inherit,
                'class' => 'validate-cedcommerce-license',
                'style' => 'float:left;',
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                //sets if it can be changed on the default level
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
                //sets if can be changed on website level
                'after_element_html' => $buttonHtml,
            ))->setRenderer($this->_getFieldRenderer());
        return $field->toHtml();
    }

    /**
     * @return DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new DataObject([
                'show_in_default' => 1,
                'show_in_website' => 1
            ]);
        }
        return $this->_dummyElement;
    }

    /**
     * @return string
     */
    protected function _getScriptHtml()
    {
        $script = '<script type="text/javascript">';
        $script .= "require([
                    'jquery', // jquery Library
                    'jquery/ui', // Jquery UI Library
                    'jquery/validate', // Jquery Validation Library
                    'mage/translate' // Magento text translate (Validation message translte as per language)
                    ], function($){ 
                    $.mage.message =  $.mage.__('Please enter a valid License Number.');
                    $.validator.addMethod(
            'validate-cedcommerce-license', function (value,licenseElement) {";
        $script .= 'var url = "' . $this->getLicenseUrl() . '?"+licenseElement.id+"="+licenseElement.value;';
        $script .= 'var formId = configForm.formId;';
        $script .= 'var ok = false;console.log(licenseElement.id);';
        $script .= "jQuery.ajax( {
                		    url: url,
                		    type: 'POST',
                		    showLoader: true,
                		    data: {form_key:jQuery('input[name=form_key]').val()},
                		    async:false
                		}).done(function(a) { 
                		    var response = a;
                			validateTrueEmailMsg = response.message;
                			if (response.success == 0) {									
                				$.mage.message = validateTrueEmailMsg;
                				alert(validateTrueEmailMsg);
                				ok = false;
                			} else {
                				ok = true; 
                			}
                		});
                		return ok;
            		  ";

        $script .= "},$.mage.message);
            });
             </script>
            ";
        return $script;
    }

    /**
     * Retrieve local license url
     *
     * @return string
     */
    public function getLicenseUrl()
    {
        if ($this->_licenseUrl === null) {
            $secure = false;
            if ($this->_helper->getStoreConfig(self::LICENSE_USE_HTTPS_PATH)) {
                $secure = true;
            }
            $this->_licenseUrl = $this->getUrl($this->_helper->getStoreConfig(self::LICENSE_VALIDATION_URL_PATH),
                array('_secure' => $secure));
        }
        return $this->_licenseUrl;
    }
}
