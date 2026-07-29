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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity;


use Ced\CsMarketplace\Model\VshopFactory;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity
 */
class Edit extends Container
{

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var VshopFactory
     */
    protected $vshopFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param VshopFactory $vshopFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        VshopFactory $vshopFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->vshopFactory = $vshopFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize form.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'vendor_id';
        $this->_blockGroup = 'Ced\CsMarketplace';
        $this->_controller = 'adminhtml_vendor_entity';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => ['mage-init' => ['button' => ['event' => 'saveAndContinueEdit',
                    'target' => '#edit_form']],]
            ],
            10
        );


        $this->buttonList->update('save', 'label', __('Save Vendor'));
        $this->buttonList->update('delete', 'label', __('Delete Vendor'));


        $this->_formScripts[] = '
            require(["jquery","prototype"], function(jQuery){
                Event.observe(window, \'load\', function(){
                    Event.observe(
                        $("save_and_continue_edit"),
                        \'click\',
                        disableSave);
                    Event.observe(
                        $("save"),
                        \'click\',
                        disableSave);
                });
                function disableSave(){
                     if(jQuery(\'#edit_form\').valid()){
                        jQuery(\'body\').loader(\'show\');
                     } else {
                        jQuery(\'body\').loader(\'hide\');
                     }
                }
            });
        ';

        if ($this->_coreRegistry->registry('vendor_data') && $this->_coreRegistry->registry('vendor_data')->getId()) {
            $vendorId = $this->_coreRegistry->registry('vendor_data')->getId();
            $model = $this->vshopFactory->create()->loadByField(array('vendor_id'), array($vendorId));

            $url = $this->getUrl('*/*/massDisable',
                array('vendor_id' => $vendorId, 'shop_disable' => \Ced\CsMarketplace\Model\Vshop::DISABLED,
                    'inline' => 1));
            $url = "deleteConfirm('" . __('Are you sure you want to Disable?') . "','" . $url . "')";
            $button = __('Disable Vendor Shop');
            $class = 'delete';

            if ($model->getId() != '' && $model->getShopDisable() == \Ced\CsMarketplace\Model\Vshop::DISABLED) {
                $url = $this->getUrl('*/*/massDisable',
                    array('vendor_id' => $vendorId, 'shop_disable' => \Ced\CsMarketplace\Model\Vshop::ENABLED,
                        'inline' => 1));
                $url = "deleteConfirm('" . __('Are you sure you want to Enable?') . "','" . $url . "')";
                $button = __('Enable Vendor Shop');
                $class = 'save';
            }

            $this->buttonList->add('shop_disable', [
                'label' => $button,
                'onclick' => $url,
                'class' => $class,
            ], -100);
        }
    }


    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $vendor = $this->_coreRegistry->registry('vendor_data');
        if ($vendor->getVendorId()) {
            return __("Edit '%1'", $this->escapeHtml($vendor->getName()));
        } else {
            return __('Add Vendor');
        }
    }

    /**
     * Retrieve products JSON
     *
     * @return string
     */
    public function getProductsJson()
    {
        return '{}';
    }

    /**
     * @return mixed
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/CheckAvailability', ['_current' => true]);
    }
}
