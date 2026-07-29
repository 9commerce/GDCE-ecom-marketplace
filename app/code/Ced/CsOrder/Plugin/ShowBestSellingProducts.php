<?php
namespace Ced\CsOrder\Plugin;

class ShowBestSellingProducts
{
    /**
     * @param \Ced\CsMarketplace\Block\Vendor\Dashboard\MostSoldProducts $subject
     * @param $result
     * @return mixed
     */
    public function afterGetBestSellerProducts(
        \Ced\CsMarketplace\Block\Vendor\Dashboard\MostSoldProducts $subject,
        $result
    ) {
        $result->getSelect()->joinLeft(
            ['vendor_sales_order' => $subject->resourceConnection->getTableName(
                'ced_csmarketplace_vendor_sales_order'
            )],
            "item_table.order_id = vendor_sales_order.real_order_id",
            ['vendor_order_approval']
        )->where('vendor_order_approval = 1');
        return $result;
    }
}
