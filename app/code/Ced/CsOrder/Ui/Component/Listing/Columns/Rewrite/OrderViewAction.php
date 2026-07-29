<?php
namespace Ced\CsOrder\Ui\Component\Listing\Columns\Rewrite;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\Order;

class OrderViewAction extends \Ced\CsMarketplace\Ui\Component\Listing\Columns\OrderViewAction{

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Order $salesOrder,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $urlBuilder, $salesOrder, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array|mixed
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['order_id'])) {
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                'csorder/vendororder/view',
                                [
                                    'vorder_id' => $item['id']
                                ]
                            ),
                            'label' => __('View Order')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
