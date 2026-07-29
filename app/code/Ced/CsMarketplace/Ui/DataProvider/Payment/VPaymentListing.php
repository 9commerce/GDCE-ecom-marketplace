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

namespace Ced\CsMarketplace\Ui\DataProvider\Payment;

use Ced\CsMarketplace\Model\ResourceModel\Vpayment\Collection;
use Ced\CsMarketplace\Model\Session;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory;

/**
 * Class VPaymentListing
 * @package Ced\CsMarketplace\Ui\DataProvider\Payment
 */
class VPaymentListing extends AbstractDataProvider
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
     * VPaymentListing constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Session $customerSession
     * @param CollectionFactory $collection
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Session $customerSession,
        CollectionFactory $collection,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection->create();
        $this->session = $customerSession->getCustomerSession();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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

        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($items['items']),
        ];
    }
}
