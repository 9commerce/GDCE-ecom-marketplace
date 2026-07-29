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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Payment;

use Ced\CsMarketplace\Model\Vsettings;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class Methods
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Payment
 */
class Methods extends Generic
{

    /**
     * @var Vsettings
     */
    protected $vsettings;

    /**
     * Methods constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Vsettings $vsettings
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Vsettings $vsettings,
        array $data = []
    )
    {
        $this->vsettings = $vsettings;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $vendor = $this->_coreRegistry->registry('vendor_data');
        if ($vendor) {
            $methods = $vendor->getPaymentMethods();
            $form = $this->_formFactory->create();
            if (count($methods) > 0) {
                $cnt = 1;
                foreach ($methods as $code => $method) {
                    $fields = $method->getFields();
                    if (count($fields) > 0) {
                        $fieldset =
                            $form->addFieldset('csmarketplace_' . $code, array('legend' => $method->getLabel('label')));
                        foreach ($fields as $id => $field) {
                            $key = strtolower(Vsettings::PAYMENT_SECTION . '/' .
                                $method->getCode() . '/' . $id);
                            $value = '';
                            if ((int)$vendor->getId()) {
                                $setting = $this->vsettings->loadByField(
                                    ['key', 'vendor_id'],
                                    [$key, (int)$vendor->getId()]
                                );
                                if ($setting) $value = $setting->getValue();
                            }

                            $res = isset($field['values']) ? $this->getLabelByValue($value, $field['values']) : $value;

                            $addFields = [
                                'label' => $method->getLabel($id),
                                'value' => $res,
                                'name' => 'groups[' . $method->getCode() . '][' . $id . ']',
                            ];

                            if (isset($field['class']))
                                $addFields['class'] = $field['class'];

                            if (isset($field['required']))
                                $addFields['required'] = $field['required'];

                            if (isset($field['onchange']))
                                $addFields['onchange'] = $field['onchange'];

                            if (isset($field['onclick']))
                                $addFields['onclick'] = $field['onclick'];

                            if (isset($field['href']))
                                $addFields['href'] = $field['href'];

                            if (isset($field['target']))
                                $addFields['target'] = $field['target'];

                            if (isset($field['values']))
                                $addFields['values'] = $field['values'];

                            $fieldset->addField(
                                $method->getCode() . $method->getCodeSeparator() . $id, 'label',
                                $addFields
                            );
                        }
                        $cnt++;
                    }
                }
            }
            $this->setForm($form);
        }
        return $this;
    }

    /**
     * retrieve label from value
     * @param string $value
     * @param array
     * @return string
     */
    protected function getLabelByValue($value = '', $values = array())
    {
        foreach ($values as $key => $option) {
            if (is_array($option)) {
                if (isset($option['value']) && $option['value'] == $value && $option['label']) {
                    return $option['label'];
                }
            } else {
                if ($key == $value && $option->getText()) {
                    return $option->getText();
                }
            }
        }
        return $value;
    }
}
