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

namespace Ced\CsMarketplace\Model\ResourceModel;

/**
 * Class Setup
 * @package Ced\CsMarketplace\Model\ResourceModel
 */
class Setup extends \Ced\CsMarketplace\Model\ResourceModel\Setup\AbstractModel
{

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendorModel;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\Form
     */
    protected $form;

    /**
     * Setup constructor.
     * @param \Ced\CsMarketplace\Model\Vendor $vendorModel
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Ced\CsMarketplace\Model\Vendor\Form $form
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vendor $vendorModel,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Ced\CsMarketplace\Model\Vendor\Form $form
    ) {
        $this->vendorModel = $vendorModel;
        $this->attributeFactory = $attributeFactory;
        $this->form = $form;
    }

    /**
     * Add vendors attributes to customer forms
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function installVendorForms()
    {
        $allowedAttributes = [
            'public_name',
            'shop_url',
            'created_at',
            'status',
            'group',
            'name',
            'gender',
            'profile_picture',
            'email',
            'contact_number',
            'company_name',
            'about',
            'company_logo',
            'company_banner',
            'company_address',
            'support_number',
            'support_email',
        ];

        $typeId = $this->vendorModel->getEntityTypeId();

        $vendorAttributes = $this->attributeFactory->create()->getCollection()
            ->addFieldToFilter('entity_type_id', ['eq' => $typeId])
            //->addFieldToFilter('attribute_code',['in'=>$allowedAttributes))
            ->setOrder('attribute_id', 'ASC');

        foreach ($vendorAttributes as $attribute) {
            $sortOrder = array_keys($allowedAttributes, $attribute->getAttributeCode());
            $sortOrder = isset($sortOrder[0]) ? $sortOrder[0] : 0;
            $visibility = in_array($attribute->getAttributeCode(), $allowedAttributes) ? 1 : 0;
            $data[] = [
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'is_visible' => $visibility,
                'sort_order' => $sortOrder,
                'store_id' => 0
            ];
        }

        if (!empty($data)) {
            $this->form->insertMultiple($data);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateVendorAttributes()
    {
        $vendorAttributes = [
            'shop_url' => ['class' => 'validate-shopurl'],
            'public_name' => ['class' => 'validate-no-html-tags'],
            'created_at' => ['class' => 'validate-no-html-tags'],
            'status' => ['class' => 'validate-no-html-tags'],
            'group' => ['class' => 'validate-no-html-tags'],
            'name' => ['class' => 'validate-no-html-tags'],
            'gender' => ['class' => 'validate-no-html-tags'],
            'profile_picture' => ['class' => 'validate-no-html-tags'],
            'email' => ['class' => 'validate-email'],
            'contact_number' => ['class' => 'validate-digits'],
            'company_name' => ['class' => 'validate-no-html-tags'],
            'about' => '',
            'company_logo' => '',
            'company_banner' => ['class' => 'validate-no-html-tags'],
            'company_address' => ['class' => 'validate-no-html-tags'],
            'support_number' => ['class' => 'validate-digits'],
            'support_email' => ['class' => 'validate-email'],
        ];
        foreach ($vendorAttributes as $code => $values) {
            $attributeModel = $this->attributeFactory->create()->loadByCode('csmarketplace_vendor', $code);
            if (isset($values['class'])) {
                $this->updateAttribute('csmarketplace_vendor', $attributeModel->getId(), 'frontend_class',
                    $values['class']);
            }
        }
    }

    /**
     * @param $attribute
     * @param int $is_visible
     * @param int $position
     */
    public function updateVendorForms($attribute, $is_visible = 0, $position = 0)
    {
        $joinFields = $this->_vendorForm($attribute);
        if (count($joinFields) > 0) {
            foreach ($joinFields as $joinField) {
                $joinField->setData('is_visible', $is_visible);
                $joinField->setData('sort_order', $position);
                $joinField->save();
            }
        }
    }

    /**
     * @param $attribute
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function _vendorForm($attribute)
    {
        $store = 0;
        $fields = $this->form->getCollection()
            ->addFieldToFilter('attribute_id', ['eq' => $attribute->getAttributeId()])
            ->addFieldToFilter('attribute_code', ['eq' => $attribute->getAttributeCode()])
            ->addFieldToFilter('store_id', ['eq' => $store]);
        if (count($fields) == 0) {
            $data[] = [
                'attribute_id' => $attribute->getId(),
                'attribute_code' => $attribute->getAttributeCode(),
                'is_visible' => 0,
                'sort_order' => 0,
                'store_id' => $store
            ];
            $this->form->insertMultiple($data);
            return $this->_vendorForm($attribute);
        }

        return $fields;
    }

    /**
     * Prepare vendor attribute values to save in additional table
     *
     * @param  array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);
        $data = array_merge(
            $data, [
                'is_visible' => $this->_getValue($attr, 'visible', 1),
                'is_system' => $this->_getValue($attr, 'system', 1),
                'input_filter' => $this->_getValue($attr, 'input_filter', null),
                'multiline_count' => $this->_getValue($attr, 'multiline_count', 0),
                'validate_rules' => $this->_getValue($attr, 'validate_rules', null),
                'data_model' => $this->_getValue($attr, 'data', null),
                'sort_order' => $this->_getValue($attr, 'position', 0)
            ]
        );

        return $data;
    }
}
