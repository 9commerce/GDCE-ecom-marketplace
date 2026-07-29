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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Observer;

use Ced\CsMarketplace\Model\VproductsFactory;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveVproductAttributesData
 * @package Ced\CsMarketplace\Observer
 */
Class SaveVproductAttributesData implements ObserverInterface
{

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * SaveVproductAttributesData constructor.
     * @param VproductsFactory $vProductsFactory
     * @param Attribute $attribute
     * @param RequestInterface $request
     */
    public function __construct(
        VproductsFactory $vProductsFactory,
        Attribute $attribute,
        RequestInterface $request
    ) {
        $this->vProductsFactory = $vProductsFactory;
        $this->attribute = $attribute;
        $this->request = $request;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $productIds = $this->attribute->getProductIds();

        if (is_array($productIds)) {
            $inventoryData = $this->request->getParam('inventory', []);
            $attributesData = $this->request->getParam('attributes', []);
            $websiteRemoveData = $this->request->getParam('remove_website_ids', []);
            $websiteAddData = $this->request->getParam('add_website_ids', []);

            $productData['product'] = (!empty($attributesData)) ? $attributesData : [];

            $vProductModal = $this->vProductsFactory->create();
            $collection = $vProductModal->getCollection()->addFieldToFilter('product_id', ['in' => $productIds]);
            if (count($collection) > 0) {
                foreach ($collection as $row) {
                    $oldWebsiteIds = $row->getWebsiteIds() ? explode(',', $row->getWebsiteIds()) : [];
                    $websiteIds = implode(',',
                        array_unique(array_filter(array_merge(
                            array_diff($oldWebsiteIds, $websiteRemoveData),
                            $websiteAddData
                        )))
                    );
                    $row->addData($productData['product']);

                    if (isset($productData['product']['stock_data'])) {
                        $productData['product']['stock_data'] = $inventoryData;
                        $row->addData($productData['product']['stock_data']);
                    }

                    if (isset($productData['product']['status'])) {
                        $row->setStoreId($this->request->getParam('store', 0));
                        $row->setStatus($productData['product']['status']);
                    }

                    $vProductModal->extractNonEditableData($row);
                    $row->addData(['website_ids' => $websiteIds]);
                    $row->save();
                }
            }
        }
    }
}
