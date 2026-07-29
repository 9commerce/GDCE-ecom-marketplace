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

namespace Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab;

/**
 * Class Qlist
 * @package Ced\CsPurchaseOrder\Block\Vendor\Edit\Tab
 */
class Qlist extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Qlist constructor.
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
        $this->setArea('adminhtml');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {

        parent::_prepareForm();
        $podata = $this->_coreRegistry->registry('porequest');
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('PO Request Information')]);

        $fieldset->addField(
            'product_name',
            'text',
            [
                'name' => 'product_name',
                'label' => __('Product Name'),
                'title' => __('Product Name'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );

        $fieldset->addField(
            'images',
            'note',
            [
                'label' => __('Images'),
                //'required' => true,
                'name' => 'images',
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                        'Ced\CsPurchaseOrder\Block\Vendor\Edit\Images'
                    )->toHtml() . '</div>'
            ]
        );


        $fieldset->addField(
            'document',
            'note',
            [
                'label' => __('Document'),
                //'required' => true,
                'name' => 'images',
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                        'Ced\CsPurchaseOrder\Block\Vendor\Edit\Document'
                    )->toHtml() . '</div>'
            ]
        );


        $fieldset->addField(
            'color',
            'text',
            [
                'name' => 'color',
                'label' => __('Product Color'),
                'title' => __('Product Color'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );

        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Requested Qty'),
                'title' => __('Requested Qty'),
                'required' => true,
                'class' => '',
                'readonly' => false,
            ]
        );

        $fieldset->addField(
            'category',
            'textarea',
            [
                'name' => 'category',
                'label' => __('Category'),
                'title' => __('Category'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );

        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Unit Price'),
                'title' => __('Unit Price'),
                'required' => true,
                'class' => '',
                'readonly' => false,
            ]
        );

        $fieldset->addField(
            'store_url',
            'text',
            [
                'name' => 'store_url',
                'label' => __('Store Url'),
                'title' => __('Store Url'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );
        $fieldset->addField(
            'item_url',
            'text',
            [
                'name' => 'item_url',
                'label' => __('Item Url'),
                'title' => __('Item Url'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );
        $fieldset->addField(
            'first_name',
            'text',
            [
                'name' => 'first_name',
                'label' => __('Custmer Name'),
                'title' => __('Customer Name'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );
        $fieldset->addField(
            'business_email',
            'text',
            [
                'name' => 'business_email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
                'required' => true,
                'class' => '',
                'readonly' => true,
            ]
        );
        $form->setValues($podata->getData());
        $this->setForm($form);
        return $this;

    }

    /**
     * @return mixed
     */
    public function getPoDetail()
    {

        return $this->_coreRegistry->registry('porequest');

    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getImageSrc()
    {
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/images/' . $this
                ->_coreRegistry->registry('porequest')->getCustomerId() . '/';
        return $url;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getFileSrc()
    {
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/files/' . $this
                ->_coreRegistry->registry('porequest')->getCustomerId() . '/';
        return $url;
    }

    /**
     * @param $helper
     * @return mixed
     */
    public Function helper($helper)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create($helper);
        return $priceHelper;
    }

}
