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

namespace Ced\CsPurchaseOrder\Block\Vendor\EditQuotations;

use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;

/**
 * Class Tabs
 * @package Ced\CsPurchaseOrder\Block\Vendor\EditQuotations
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var \Ced\Customer\Model\Session
     */
    protected $session;

    /**
     * Tabs constructor.
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    )
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->session = $session;
    }

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('grid_records');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Quotations List'));
        $this->setData('area', 'adminhtml');

    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $vendor_status = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('vendor_id', $this->session->getVendorId())
            ->getLastItem()
            ->getVendorStatus();

        $this->addTab(
            'assigned_details',
            [
                'label' => __('Quotations Details'),
                'title' => __('Quotations Details'),
                'content' => $this->getLayout()
                    ->createBlock('Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab\AssignedList')
                    ->setTemplate('Ced_CsPurchaseOrder::purchaseorder/main.phtml')
                    ->toHtml(),
            ]
        );

        $this->addTab(
            'vendor_negotiation',
            [
                'label' => __('Negotiation Section'),
                'title' => __('Negotiation Section'),
                'content' => $this->getLayout()
                    ->createBlock('Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab\Negotiations')->toHtml(),

            ]
        );

        if ($vendor_status == VendorStatus::NEW || $vendor_status == VendorStatus::UPDATED_BY_CUSTOMER
            || $vendor_status == VendorStatus::UPDATED_BY_VENDOR) {

            $this->addTab(
                'vendor_front',
                [
                    'label' => __('Comments'),
                    'title' => __('Comments'),
                    'content' => $this->getLayout()
                        ->createBlock('Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab\Comments')
                        ->toHtml(),
                ]
            );
        }

        $this->addTab(
            'vendor_history',
            [
                'label' => __('Chat History'),
                'title' => __('Chat History'),
                'content' => $this->getLayout()
                    ->createBlock('Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab\History')
                    ->setTemplate('Ced_CsPurchaseOrder::purchaseorder/quotations/chat_history.phtml')
                    ->toHtml(),
            ]
        );
        return parent::_beforeToHtml();
    }
}