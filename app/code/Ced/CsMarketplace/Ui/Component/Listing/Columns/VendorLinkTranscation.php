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

namespace Ced\CsMarketplace\Ui\Component\Listing\Columns;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\ResourceModel\Vendor;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Class VendorLinkTranscation
 * @package Ced\CsMarketplace\Ui\Component\Listing\Columns
 */
class VendorLinkTranscation extends Column
{


    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;


    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        VendorFactory $vendorFactory,
        Vendor $vendorResourceModel,
        Order $salesOrder,
        OrderItemRepositoryInterface $orderItemRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->vendorFactory = $vendorFactory;
        $this->vendorResourceModel = $vendorResourceModel;
        $this->salesOrder = $salesOrder;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['vendor_id']) {
                    if ($item['vendor_name']=== null) {
                        $array_data = json_decode($item['amount_desc'], true);
                        $firstKey = array_key_first($array_data);
                        $orderInfo = $this->salesOrder->loadByIncrementId($firstKey);
                        foreach ($orderInfo->getAllItems() as $item1) {
                            $options = $item1->getProductOptions();
                            if (!empty($options['info_buyRequest'])) {
                                if (!empty($options['additional_options'][0]['value'])) {
                                    $vendor_quote_name = $options['additional_options'][0]['value'];
                                    $item[$this->getData('name')] = $vendor_quote_name;
                                }
                            }
                            break;
                        }
                    } else {
                        $vendorLink = $this->urlBuilder->getUrl('csmarketplace/vendor/edit', ['vendor_id' => $item['vendor_id']]);
                        $item[$this->getData('name')] = '<a target = "_blank" href="' . $vendorLink . '">' . $item['vendor_name'] . '</a>';
                    }
                }
            }
        }
        return $dataSource;
    }
}
