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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab;

/**
 * Class Comments
 * @package Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab
 */
class Comments extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Comments constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->setData('area', 'adminhtml');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {

        parent::_prepareForm();
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Comments')]);

        $fieldset->addField(
            'comments',
            'textarea',
            [
                'name' => 'comments',
                'label' => __('Comments'),
                'title' => __('Comments'),
                'class' => 'validate-text',
                'maxlength'=> "8000"
            ]
        );
        $this->setForm($form);
        return $this;

    }

}
