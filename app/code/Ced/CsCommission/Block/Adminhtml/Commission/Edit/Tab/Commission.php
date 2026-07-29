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

namespace Ced\CsCommission\Block\Adminhtml\Commission\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Commission extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * Commission constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_categoryFactory = $categoryFactory;
        $this->_vendorFactory = $vendorFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Commission');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Commission');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var  $model */
        $model = $this->_coreRegistry->registry('cscommission_commission');
        $isElementDisabled = false;
        /** @var  $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Commission')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'category',
            'select',
            [
                'name' => 'category',
                'label' => __('Category'),
                'title' => __('Category'),
                'options' => $this->_getCategoryOptions(),
                'required' => true,
                'after_element_html' => $this->getAfterElementHtml()
            ]
        );
        $fieldset->addField(
            'method',
            'select',
            [
                'name' => 'method',
                'label' => __('Calculation Method'),
                'title' => __('Calculation Method'),
                'required' => true,
                'options' => ['fixed' => 'Fixed', 'percentage' => 'Percentage']
            ]
        );
        $fieldset->addField(
            'fee',
            'text',
            [
                'name' => 'fee',
                'label' => __('Commission Fee'),
                'title' => __('Commission Fee'),
                'required' => true,
            ]
        );
        $script = $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'class' => 'validate-zero-or-greater',
                'required' => true,
            ]
        );

        $script->setAfterElementHtml("<script>
            function disableButton() {
                var btn = document.getElementsByClassName('save');
                for(var i = 0, length = btn.length; i < length; i++) {
                    if (document.querySelector('input[name=\"fee\"]').value!='' &&
                        document.querySelector('input[name=\"priority\"]').value!='')
                    {
                        btn[i].disabled = true;
                    }
                }
            }
        </script>");

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    protected function _getCategoryOptions()
    {
        $items = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )->load()->getItems();

        $result = [];
        foreach ($items as $item) {
            $result[$item->getEntityId()] = $item->getName() . '-' . $item->getId();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getAfterElementHtml()
    {
        $widgetOptions = $this->_jsonEncoder->encode(
            [
                'suggestOptions' => [
                    'source' => $this->getUrl('catalog/category/suggestCategories'),
                    'valueField' => '#category',
                    'className' => 'category-select',
                    'multiselect' => false,
                    'showAll' => true,
                ],
                'saveCategoryUrl' => $this->getUrl('catalog/category/save'),
            ]
        );
        //TODO: JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
                <script>
                require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
                    $(function(){ // waiting for page to load to have '#category_ids-template' available
                        $('#new-category').mage('newCategoryDialog', $widgetOptions);
                    });
                });
                </script>
HTML;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get vendor options
     *
     * @return array
     */
    protected function _getVendorOptions()
    {
        $items = $this->_vendorFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )->load()->getItems();

        $result = [];
        $result[0] = 'All';
        foreach ($items as $item) {
            $result[$item->getEntityId()] = $item->getName();
        }

        return $result;
    }
}
