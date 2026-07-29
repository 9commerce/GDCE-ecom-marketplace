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

use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;

/**
 * Class Negotiations
 * @package Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab
 */
class Negotiations extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ProductNames
     */
    protected $names;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var VendorStatusCollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * Negotiations constructor.
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Ced\CsPurchaseOrder\Model\ProductNames $names
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Ced\CsPurchaseOrder\Model\ProductNames $names,
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->sessionFactory = $sessionFactory;
        $this->names = $names;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $negotiations = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('vendor_id', $this->sessionFactory->create()->getVendorId())
            ->getLastItem();
        $qty = $negotiations->getNegotiationQty();
        $price = $negotiations->getNegotiationPrice();

        if ($qty == null && $price == null) {
            $purchaseorder = $this->purchaseorderFactory->create()
                ->load($this->getRequest()->getParam('id'));

            $qty = $purchaseorder->getProposedQty();
            $price = $purchaseorder->getPreferredPricePerQty();

        }

        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Negotiations Section')]);

        $fieldset->addField(
            'product_id',
            'select',
            [
                'name' => 'product_id',
                'label' => __('Product'),
                'title' => __('Product'),
                'required' => true,
                'values' => $this->names->getOptions(),
            ]
        );

        $fieldset->addField(
            'nqty',
            'text',
            [
                'name' => 'nqty',
                'label' => __('Qty'),
                'title' => __('Qty'),
                'class' => 'validate-number validate-greater-than-zero integer',
                'disabled' => false,
                'required' => true,
                'value' => $qty,
            ]
        );

        $fieldset->addField(
            'nprice',
            'text',
            [
                'name' => 'nprice',
                'label' => __('Price'),
                'title' => __('Price'),
                'class' => 'validate-number validate-greater-than-zero',
                'disabled' => false,
                'required' => true,
                'value' => round($price, 2)
            ]
        );
        if ($negotiations->getVendorStatus() != VendorStatus::NEW) {
            $value = [];
            $value['product_id'] = $negotiations->getProductId();
            $value['nqty'] = $negotiations->getNegotiationQty();
            $value['nprice'] = (empty($negotiations->getNegotiationPrice())) ? 0 : round($negotiations->getNegotiationPrice(), 2);
            $form->setValues($value);
        }
        $this->setForm($form);
        return $this;
    }

}
