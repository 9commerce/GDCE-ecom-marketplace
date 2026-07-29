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

namespace Ced\CsPurchaseOrder\Block\Vendor;

/**
 * Class Edit
 * @package Ced\CsPurchaseOrder\Block\Vendor
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Ced\CsPurchaseOrder\Model\Purchaseorder
     */
    public $purchaseOrder;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Ced\CsPurchaseOrder\Model\Purchaseorder $purchaseOrder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Ced\CsPurchaseOrder\Model\Purchaseorder $purchaseOrder,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->purchaseOrder = $purchaseOrder;
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'ced_csPurchaseOrder';
        $this->_controller = 'vendor';

        parent::_construct();

        $this->buttonList->remove('back');
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'class' => 'back',
                'onclick' => sprintf("location.href = '%s';",
                    $this->getUrl('cspurchaseorder/quotations/qlist/')),
                'level' => -1
            ],
            -100
        );

        $this->buttonList->remove('delete');
        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'class' => 'delete',
                'onclick' => sprintf("location.href = '%s';",
                    $this->getUrl('cspurchaseorder/quotations/delete/',
                        array('id' => $this->getRequest()->getParam('id')))),
                'level' => -1
            ],
            -100
        );

        $this->buttonList->remove('reset');
    }

    /**
     * {@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }


    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $model = $this->purchaseOrder->load($this->getRequest()->getParam('id'));

        return $this->getUrl(
            'cspurchaseorder/quotations/save',
            ['_current' => true, 'back' => null, 'id' => $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->getUrl('*/*/status', ['id' => $this->getRequest()->getParam('id')]);
    }

}