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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';

    const ALT_FIELD = 'name';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Thumbnail constructor.
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new \Magento\Framework\DataObject($item);

                if ($product->getThumbnail() && $product->getThumbnail() != "no_selection") {

                    $imageUrl = $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($product->getSmallImage())
                        ->resize(80)
                        ->getUrl();
                    $item[$fieldName . '_src'] = $imageUrl;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageHelper->getLabel();
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'csproduct/vproducts/edit',
                        [
                            'id' => $product->getEntityId(),
                            'set' => $item['attribute_set_id'],
                            'type' => $item['type_id'],
                            'store' => $this->context->getRequestParam('store')
                        ]
                    );
                    $item[$fieldName . '_orig_src'] = $imageUrl;

                } else {
                    $imageUrl = $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($product->getSmallImage())
                        ->resize(80)
                        ->getUrl();
                    $item[$fieldName . '_src'] = $imageUrl;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageHelper->getLabel();
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'csproduct/vproducts/edit',
                        [
                            'id' => $product->getEntityId(),
                            'set' => $item['attribute_set_id'],
                            'type' => $item['type_id'],
                            'store' => $this->context->getRequestParam('store')
                        ]
                    );
                    $item[$fieldName . '_orig_src'] = $imageUrl;
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
