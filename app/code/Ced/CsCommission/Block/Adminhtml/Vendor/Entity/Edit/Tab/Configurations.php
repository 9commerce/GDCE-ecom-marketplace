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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Config\Model\Config\Structure\Element\Group;

class Configurations extends \Magento\Config\Block\System\Config\Form implements TabInterface
{
    /**
     * Configurations constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Block\System\Config\Form\Fieldset\Factory $fieldsetFactory
     * @param \Magento\Config\Block\System\Config\Form\Field\Factory $fieldFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Block\System\Config\Form\Fieldset\Factory $fieldsetFactory,
        \Magento\Config\Block\System\Config\Form\Field\Factory $fieldFactory,
        array $data = []
    ) {
        $this->_configStructure = $configStructure;
        $this->_coreRegistry = $registry;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $configFactory,
            $configStructure,
            $fieldsetFactory,
            $fieldFactory
        );
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Commission Configuration');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Commission Configuration');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return !$this->canShowButton();
    }

    /**
     * @return bool
     */
    public function canShowButton()
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getAfter()
    {
        return 'vpayments';
    }

    /**
     * @param Fieldset $fieldset
     * @param Group $group
     * @param Section $section
     * @param string $fieldPrefix
     * @param string $labelPrefix
     * @return $this|\Magento\Config\Block\System\Config\Form
     */
    public function initFields(
        Fieldset $fieldset,
        Group $group,
        Section $section,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $extraConfigGroups = [];

        /** @var $element \Magento\Config\Model\Config\Structure\Element\Field */
        foreach ($group->getChildren() as $element) {
            if ($element instanceof \Magento\Config\Model\Config\Structure\Element\Group) {
                $this->_initGroup($element, $section, $fieldset);
            } else {
                $path = $element->getConfigPath() ?: $element->getPath($fieldPrefix);

                if ($this->_coreRegistry->registry('vendor_data') &&
                    is_object($this->_coreRegistry->registry('vendor_data')) &&
                    $this->_coreRegistry->registry('vendor_data')->getId()
                ) {
                    $path = 'v' . $this->_coreRegistry->registry('vendor_data')->getId() . '/' . $path;
                }
                if ($element->getSectionId() != $section->getId()) {
                    $groupPath = $element->getGroupPath();
                    if (!isset($extraConfigGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject->extendConfig(
                            $groupPath,
                            false,
                            $this->_configData
                        );
                        $extraConfigGroups[$groupPath] = true;
                    }
                }
                $this->_initElement($element, $fieldset, $path, $fieldPrefix, $labelPrefix);
            }
        }
        return $this;
    }

    /**
     * @return $this|\Magento\Config\Block\System\Config\Form
     */
    protected function _initObjects()
    {
        parent::_initObjects();
        return $this;
    }

    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Field $field
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $path
     * @param string $fieldPrefix
     * @param string $labelPrefix
     */
    protected function _initElement(
        \Magento\Config\Model\Config\Structure\Element\Field $field,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        $path,
        $fieldPrefix = '',
        $labelPrefix = ''
    ) {
        $data = null;
        if (array_key_exists($path, $this->_configData)) {
            $data = $this->_configData[$path];
        } elseif ($field->getConfigPath() !== null) {
            $data = $this->getConfigValue($field->getConfigPath());
        } else {
            $data = $this->getConfigValue($path);
        }

        $fieldRendererClass = $field->getFrontendModel();
        if ($fieldRendererClass) {
            $fieldRenderer = $this->_layout->getBlockSingleton($fieldRendererClass);
        } else {
            $fieldRenderer = $this->_fieldRenderer;
        }

        $fieldRenderer->setForm($this);
        $fieldRenderer->setConfigData($this->_configData);

        $elementName = $this->_generateElementName($field->getPath(), $fieldPrefix);
        $elementId = $this->_generateElementId($field->getPath($fieldPrefix));

        if ($field->hasBackendModel()) {
            $backendModel = $field->getBackendModel();
            $backendModel->setPath(
                $path
            )->setValue(
                $data
            )->setWebsite(
                $this->getWebsiteCode()
            )->setStore(
                $this->getStoreCode()
            )->afterLoad();
            $data = $backendModel->getValue();
        }

        $dependencies = $field->getDependencies($fieldPrefix, $this->getStoreCode());
        $this->_populateDependenciesBlock($dependencies, $elementId, $elementName);

        $sharedClass = $this->_getSharedCssClass($field);
        $requiresClass = $this->_getRequiresCssClass($field, $fieldPrefix);

        $formField = $fieldset->addField(
            $elementId,
            $field->getType(),
            [
                'name' => $elementName,
                'label' => $field->getLabel($labelPrefix),
                'comment' => $field->getComment($data),
                'tooltip' => $field->getTooltip(),
                'hint' => $field->getHint(),
                'value' => $data,
                'inherit' => $this->isInherit($path),
                'class' => $field->getFrontendClass() . $sharedClass . $requiresClass,
                'field_config' => $field->getData(),
                'scope' => $this->getScope(),
                'scope_id' => $this->getScopeId(),
                'scope_label' => $this->getScopeLabel($field),
                'can_use_default_value' => true,
                'can_use_website_value' => false
            ]
        );
        $field->populateInput($formField);

        if ($field->hasValidation()) {
            $formField->addClass($field->getValidation());
        }
        if ($field->getType() == 'multiselect') {
            $formField->setCanBeEmpty($field->canBeEmpty());
        }
        if ($field->hasOptions()) {
            $formField->setValues($field->getOptions());
        }
        $formField->setRenderer($fieldRenderer);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            $paths = 'v' . $this->_coreRegistry->registry('vendor_data')->getId() . '/' . $path;
            if ($this->_scopeConfig->getValue($paths, $this->getScope(), $this->getScopeCode()) != null) {
                return $this->_scopeConfig->getValue($paths, $this->getScope(), $this->getScopeCode());
            }
        }

        return $this->_scopeConfig->getValue($path, $this->getScope(), $this->getScopeCode());
    }

    /**
     * @param $path
     * @return bool
     */
    public function isInherit($path)
    {
        if ($this->_coreRegistry->registry('vendor_data') &&
            is_object($this->_coreRegistry->registry('vendor_data')) &&
            $this->_coreRegistry->registry('vendor_data')->getId()
        ) {
            $data = $this->_scopeConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            if ($data != '') {
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * @return \Magento\Config\Block\System\Config\Form|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        try {
            $this->initForm();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
        }
    }

    /**
     * @return $this|\Magento\Config\Block\System\Config\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initForm()
    {
        $this->_initObjects();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $current = $this->getSectionCode();
        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        $website = $this->getWebsiteCode();
        $store = $this->getStoreCode();
        if ($section) {
            foreach ($section->getChildren() as $group) {
                if ($group->getId() == 'vpayments') {
                    $this->_initGroup($group, $section, $form);
                }
            }
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * @return string
     */
    public function getSectionCode()
    {
        return 'ced_csmarketplace';
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getRequest()->isAjax()) {
            return parent::_toHtml();
        }
        $parent = '<div id="vendor_group_configurations_section">' . parent::_toHtml() . '</div>';
        if (strlen($parent) <= 50) {
            $parent .= '<div id="messages"><ul class="messages"><li class="error-msg"><ul><li><span>' .
                __('No Configurations are Available for Current Configuration Scope.
                Please Up the Configuration Scope by One Level.') . '</span></li></ul></li></ul></div>';
            return $parent;
        }
        return $parent;
    }
}
