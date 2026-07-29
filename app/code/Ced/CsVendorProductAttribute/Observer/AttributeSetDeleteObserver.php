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
 * @category     Ced
 * @package      Ced_CsVendorProductAttribute
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright    Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AttributeSetDeleteObserver
 * @package Ced\CsVendorProductAttribute\Observer
 */
class AttributeSetDeleteObserver implements ObserverInterface
{
    /**
     * @var \Ced\CsVendorProductAttribute\Model\Attributeset
     */
    protected $attributeset;

    /**
     * AttributeSetDeleteObserver constructor.
     * @param \Ced\CsVendorProductAttribute\Model\Attributeset $attributeset
     */
    public function __construct(\Ced\CsVendorProductAttribute\Model\Attributeset $attributeset)
    {
        $this->attributeset = $attributeset;
    }

    /**
     * Delete Attribute Set From Vendor's table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute_set = $observer->getEvent()->getObject();
        $attribute_set_id = $attribute_set->getId();
        $attr_set_model = $this->attributeset->getCollection()
            ->addFieldToFilter('attribute_set_id', $attribute_set_id)->getFirstItem();
        $attr_set_model->delete();
        return $this;
    }
}
