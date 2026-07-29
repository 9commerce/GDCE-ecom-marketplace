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
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\DataProvider\Order;

use Ced\CsMarketplace\Model\ResourceModel\Vorders\Collection;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\Vorders;
use Magento\Framework\View\Element\Context;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;

/**
 * Class VOrderListing
 * @package Ced\CsMarketplace\Ui\DataProvider\Order
 */
class VOrderListing extends AbstractDataProvider
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Vorders
     */
    protected $vorders;

    /**
     * VOrderListing constructor.
     * @param Session $customerSession
     * @param CollectionFactory $collection
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        CollectionFactory $collection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->session = $customerSession->getCustomerSession();
        $this->collection = $collection->create();
        $this->collection->getSelect()->columns([
            'net_earned' => new \Zend_Db_Expr(
                "main_table.base_order_total - main_table.shop_commission_base_fee"
            )
        ]);
        $this->collection->addFilterToMap('net_earned', new \Zend_Db_Expr(
            "(main_table.base_order_total - main_table.shop_commission_base_fee)"
        ));
    }

    /**
     * @return array
     */
    public function getData()
    {
        $vendorId = $this->session->getVendorId();
        if (!$this->collection->isLoaded())
        {
            $this->collection = $this->collection->addFieldToFilter('vendor_id', $vendorId);
        }
        $items = $this->collection->toArray();
        // foreach ($items['items'] as $key => $itemValues)
        // {
        //     if ($itemValues['payment_state'] == 4 || $itemValues['payment_state'] ==5)
        //         $items['items'][$key]['order_payment_state'] = $itemValues['payment_state'];
        // }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($items['items']),
        ];
    }
}
