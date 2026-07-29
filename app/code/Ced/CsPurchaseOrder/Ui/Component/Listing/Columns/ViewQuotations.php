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
 * @package   Ced_CsPurchaseOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;

/**
 * Class ViewQuotations
 * @package Ced\CsPurchaseOrder\Ui\Component\Listing\Columns
 */
class ViewQuotations extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * ViewQuotations constructor.
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                    if($item['status'] == VendorStatus::UPDATED_BY_CUSTOMER ||
                        $item['status'] == VendorStatus::UPDATED_BY_VENDOR ||
                        $item['status'] == VendorStatus::NEW)
                    $canView = 'Edit';
                else
                    $canView = 'View';
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'cspurchaseorder/quotations/assignedview',
                        ['id' => $item['id'], 'store' => $storeId]
                    ),
                    'label' => __($canView),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}