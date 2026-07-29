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

namespace Ced\CsMarketplace\Block\Vsettings\Payment;

use Ced\CsMarketplace\Block\Vendor\Profile\Edit;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\Vendor\AttributeFactory;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\Vsettings;
use Ced\CsMarketplace\Model\VsettingsFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class Methods
 * @package Ced\CsMarketplace\Block\Vsettings\Payment
 */
class Methods extends Edit
{

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var VsettingsFactory
     */
    protected $vsettingsFactory;

    /**
     * Methods constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param AttributeFactory $attributeFactory
     * @param StoreManagerInterface $storeManager
     * @param Timezone $timezone
     * @param Data $csmarketplaceHelper
     * @param VsettingsFactory $vsettingsFactory
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context, Session $customerSession,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        AttributeFactory $attributeFactory,
        StoreManagerInterface $storeManager,
        Timezone $timezone,
        Data $csmarketplaceHelper,
        VsettingsFactory $vsettingsFactory
    ) {
        $this->_formFactory = $formFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vsettingsFactory = $vsettingsFactory;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory, $formFactory,
            $attributeFactory, $storeManager, $timezone);
    }

    /**
     * Prepare form before rendering HTML
     * @return $this|Edit
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $vendor = $this->getVendorId() ? $this->getVendor() : array();
        if ($vendor && $vendor->getId()) {
            $methods = $vendor->getPaymentMethods();
            $form = $this->_formFactory->create();
            $form->setAction($this->getUrl('*/*/save',
                array('section' => Vsettings::PAYMENT_SECTION)))
                ->setId('form-validate')
                ->setMethod('POST')
                ->setEnctype('multipart/form-data')
                ->setUseContainer(true);
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
                            $vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');
                            $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
                            $setting = $this->vsettingsFactory->create()
                                ->loadByField(array($key_tmp, $vendor_id), array($key, (int)$vendor->getId()));
                            if ($setting) $value = $setting->getValue();
                            $fieldset->addField($method->getCode() . $method->getCodeSeparator() . $id,
                                isset($field['type']) ? $field['type'] : 'text', array(
                                    'label' => $method->getLabel($id),
                                    'value' => $value,
                                    'name' => 'groups[' . $method->getCode() . '][' . $id . ']',
                                    isset($field['class']) ? 'class' : '' => isset($field['class']) ? $field['class'] :
                                        '',
                                    isset($field['required']) ? 'required' : '' => isset($field['required']) ?
                                        $field['required'] : '',
                                    isset($field['onchange']) ? 'onchange' : '' => isset($field['onchange']) ?
                                        $field['onchange'] : '',
                                    isset($field['onclick']) ? 'onclick' : '' => isset($field['onclick']) ?
                                        $field['onclick'] : '',
                                    isset($field['href']) ? 'href' : '' => isset($field['href']) ? $field['href'] : '',
                                    isset($field['target']) ? 'target' : '' => isset($field['target']) ?
                                        $field['target'] : '',
                                    isset($field['values']) ? 'values' : '' => isset($field['values']) ?
                                        $field['values'] : '',
                                    isset($field['after_element_html']) ? 'after_element_html' :
                                        '' => isset($field['after_element_html']) ?
                                        '<div><small>' . $field['after_element_html'] . '</small></div>' : '',
                                ));
                        }
                        $cnt++;
                    }
                }
            }
            $this->setForm($form);
        }
        return $this;
    }
}
