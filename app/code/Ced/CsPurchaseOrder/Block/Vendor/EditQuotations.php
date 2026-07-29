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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Block\Vendor;

use Ced\CsMarketplace\Model\Vendor;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;

/**
 * Class EditQuotations
 * @package Ced\CsPurchaseOrder\Block\Vendor
 */
class EditQuotations extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * EditQuotations constructor.
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {

        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->sessionFactory = $sessionFactory;
        parent::__construct($context, $data);
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

        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';",
                    $this->getUrl('cspurchaseorder/quotations/viewassigned')),
                'class' => 'back',

            ],
            -1
        );

        if ($this->getNegotiatedData()->getVendorStatus() == VendorStatus::NEW ||
            $this->getNegotiatedData()->getVendorStatus() == VendorStatus::UPDATED_BY_CUSTOMER) {
        $this->addButton(
            'decline',
            [
                'label' => __('Decline'),
                'on_click' => sprintf("location.href = '%s';",
                    $this->getUrl('cspurchaseorder/quotations/decline',
                        ['id' => $this->getRequest()->getParam('id')])),
                'class' => 'decline',

            ],
            -1
        );
        }

        if ($this->getNegotiatedData()->getVendorStatus() == VendorStatus::UPDATED_BY_CUSTOMER) {
            $this->addButton(
                'approve',
                [
                    'label' => __('Approve'),
                    'on_click' => sprintf("location.href = '%s';",
                        $this->getUrl('cspurchaseorder/quotations/acceptbyVendor',
                            ['requestid' => $this->getRequest()->getParam('id'),
                                'status_id' => $this->getNegotiatedData()->getId(),
                                'product_id' => $this->getNegotiatedData()->getProductId()])),
                    'class' => 'approve',

                ],
                -1
            );
        }

        if ($this->getNegotiatedData()->getVendorStatus() != VendorStatus::NEW &&
            $this->getNegotiatedData()->getVendorStatus() != VendorStatus::UPDATED_BY_CUSTOMER &&
            $this->getNegotiatedData()->getVendorStatus() != VendorStatus::UPDATED_BY_VENDOR) {

            $this->buttonList->remove('save');
        }

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
        return $this->getUrl(
            'cspurchaseorder/quotations/savequotation',
            ['_current' => true, 'back' => null, 'id' => $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'cspurchaseorder/quotations/viewassigned',
            ['_current' => true]
        );
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getNegotiatedData()
    {
        $status = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('vendor_id', $this->sessionFactory->create()->getVendorId())
            ->getLastItem();

        return $status;
    }

}