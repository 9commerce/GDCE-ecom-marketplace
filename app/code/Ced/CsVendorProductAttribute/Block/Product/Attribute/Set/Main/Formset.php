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

namespace Ced\CsVendorProductAttribute\Block\Product\Attribute\Set\Main;

/**
 * Class Formset
 * @package Ced\CsVendorProductAttribute\Block\Product\Attribute\Set\Main
 */
class Formset extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_setFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Ced\CsVendorProductAttribute\Model\ResourceModel\Attributeset\Collection
     */
    protected $attributeset;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }

    /**
     * Formset constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Ced\CsVendorProductAttribute\Model\ResourceModel\Attributeset\Collection $attributeset
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Ced\CsVendorProductAttribute\Model\ResourceModel\Attributeset\Collection $attributeset,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->session = $session;
        $this->attributeset = $attributeset;
        $this->_setFactory = $setFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepares attribute set form
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $data = $this->_setFactory->create()->load($this->getRequest()->getParam('id'));

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('set_name', ['legend' => __($this->getLegend())]);
        $fieldset->addField(
            'attribute_set_name',
            'text',
            [
                'label' => __('Name'),
                'note' => __('For internal use'),
                'name' => 'attribute_set_name',
                'required' => true,
                'class' => 'required-entry validate-no-html-tags',
                'value' => $data->getAttributeSetName()
            ]
        );

        if (!$this->getRequest()->getParam('id', false)) {
            $fieldset->addField('gotoEdit', 'hidden', ['name' => 'gotoEdit', 'value' => '1']);

            $vendorSets = $this->attributeset->addFieldToFilter('vendor_id',
                $this->session->getVendorId())->getColumnValues('attribute_set_id');

            $allowedSet = $this->scopeConfig->getValue('ced_csmarketplace/general/set',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $allowedSet = explode(',', $allowedSet??'');


            $attributesets = array_merge($allowedSet, $vendorSets);

            $sets = $this->_setFactory->create()->getResourceCollection()->setEntityTypeFilter(
                $this->_coreRegistry->registry('entityType')
            )->addFieldToFilter('attribute_set_id', ['in' => $attributesets])->load()->toOptionArray();

            $fieldset->addField(
                'skeleton_set',
                'select',
                [
                    'label' => __('Based On'),
                    'name' => 'skeleton_set',
                    'required' => true,
                    'class' => 'required-entry',
                    'values' => $sets
                ]
            );
        }

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('set-prop-form');
        $setId = $this->getRequest()->getParam('id');
        if ($setId)
            $form->setAction($this->getUrl('csvendorproductattribute/*/save', ['id' => $setId]));
        else
            $form->setAction($this->getUrl('csvendorproductattribute/*/save'));

        $form->setOnsubmit('return false;');
        $this->setForm($form);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLegend()
    {
        $setId = $this->getRequest()->getParam('id');
        if ($setId)
            return __('Edit Attribute Set Name');
        else
            return __('Create New Attribute Set');
    }
}
