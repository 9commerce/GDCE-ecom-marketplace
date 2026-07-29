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
 * @category  Ced
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit;

use Ced\CsMarketplace\Model\Vendor;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Model\Auth\Session;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\AbstractBlock;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Tabs constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Vendor $vendor
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param CollectionFactory $groupCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Vendor $vendor,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        CollectionFactory $groupCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_vendor = $vendor;
        $this->_setColFactory = $setColFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->setId('vendor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }

    /**
     * @return Widget|AbstractBlock
     * @throws LocalizedException
     */
    protected function _beforeToHtml()
    {
        try {
            $entityTypeId = $this->_vendor->getEntityTypeId();
            $setIds = $this->_setColFactory->create()->setEntityTypeFilter($entityTypeId)
                ->getAllIds();
            $groupCollection = $this->groupCollectionFactory->create();
            if (!empty($setIds)) {
                $groupCollection->addFieldToFilter('attribute_set_id', ['in' => $setIds]);
            }

            $groupCollection->setSortOrder()->load();
            foreach ($groupCollection as $group) {
                $attributes = $this->_vendor->getAttributes($group->getId(), true);
                $attributesCount = count($attributes);
                if ($attributesCount == 0) {
                    continue;
                }

                $this->addTab(
                    'group_' . $group->getId(),
                    [
                        'label' => __($group->getAttributeGroupName()),
                        'content' => $this->getLayout()->createBlock(
                            $this->getAttributeTabBlock(),
                            'csmarketplace.adminhtml.vendor.entity.edit.tab.attributes.' . $group->getId()
                        )->setGroup($group
                            ->setGroupAttributes($attributes)
                            ->toHtml())
                    ]
                );
            }

            if ($vendor_id = $this->getRequest()->getParam('vendor_id', 0)) {
                $this->addTab(
                    'payment_details',
                    [
                        'label' => __('Payment Details'),
                        'content' => $this->getLayout()->createBlock(
                            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Payment\Methods::class
                        )->toHtml()
                    ]
                );

                $this->addTab(
                    'vproducts',
                    [
                        'label' => __('Vendor Products'),
                        'title' => __('Vendor Products'),
                        'content' => $this->getLayout()->createBlock(
                            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vproducts::class
                        )->toHtml(),
                    ]
                );
                $this->addTab(
                    'vorders',
                    [
                        'label' => __('Vendor Orders'),
                        'title' => __('Vendor Orders'),
                        'content' => $this->getLayout()->createBlock(
                            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vorders::class
                        )->toHtml()
                    ]
                );
                $this->addTab(
                    'vpayments',
                    [
                        'label' => __('Vendor Transactions'),
                        'title' => __('Vendor Transactions'),
                        'content' => $this->getLayout()->createBlock(
                            \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Vpayments::class
                        )->toHtml()
                    ]
                );
                $this->addTab(
                    'commission',
                    [
                        'label' => __('Commission Configurations'),
                        'title' => __('Commission Configurations'),
                        'content' => $this->getLayout()->createBlock(
                            \Ced\CsCommission\Block\Adminhtml\Vendor\Entity\Edit\Tab\Configurations::class
                        )->toHtml()
                    ]
                );
            }
        } catch (Exception $e) {
            throw new LocalizedException($e->getMessage());
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Edit\Tab\Information::class;
    }
}
