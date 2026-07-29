<?php
namespace Ced\CsOrder\Plugin;

class ShowApprovedOrders
{
    /**
     * @param \Ced\CsMarketPlace\Model\Vendor $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAssociatedOrders(\Ced\CsMarketPlace\Model\Vendor $subject, $result)
    {
        return  $result->addFieldToFilter('vendor_order_approval', ['eq' => 1]);
    }
}
