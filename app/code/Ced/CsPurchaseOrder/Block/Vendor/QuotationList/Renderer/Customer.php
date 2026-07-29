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

namespace Ced\CsPurchaseOrder\Block\Vendor\QuotationList\Renderer;

use \Magento\Backend\Block\Context;
/**
 * Class Customer
 * @package Ced\CsPurchaseOrder\Block\Vendor\QuotationList\Renderer
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->customerFactory = $customerFactory;
    }

    /**
     * Render approval link in each vendor row
     * @param Varien_Object $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($row->getCustomerEmail());
        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }
}