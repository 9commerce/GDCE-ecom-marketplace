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

namespace Ced\CsProduct\Controller\Product\Initialization\Helper\Plugin;

use Magento\Bundle\Api\Data\OptionInterfaceFactory as OptionFactory;
use Magento\Bundle\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Framework\App\RequestInterface;

class Bundle
{
    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;

    /**
     * @var RequestInterface
     */
    protected $cedrequest;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param RequestInterface $cedrequest
     * @param OptionFactory $optionFactory
     * @param LinkFactory $linkFactory
     * @param ProductRepository $cedproductRepository
     * @param ProductCustomOptionInterfaceFactory $customOptionFactory
     */
    public function __construct(
        RequestInterface $cedrequest,
        OptionFactory $optionFactory,
        LinkFactory $linkFactory,
        ProductRepository $cedproductRepository,
        ProductCustomOptionInterfaceFactory $customOptionFactory
    ) {
        $this->cedrequest = $cedrequest;
        $this->optionFactory = $optionFactory;
        $this->linkFactory = $linkFactory;
        $this->productRepository = $cedproductRepository;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * Setting Bundle Items Data to product for further processing
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $cedproduct
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterInitialize(
        \Ced\CsProduct\Controller\Vproducts\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $cedproduct
    ) {
        $compositeReadonly = $cedproduct->getCompositeReadonly();
        $result['bundle_selections'] = $result['bundle_options'] = [];
        if (isset($this->cedrequest->getPost('bundle_options')['bundle_options'])) {
            foreach ($this->cedrequest->getPost('bundle_options')['bundle_options'] as $key => $option) {
                if (empty($option['bundle_selections'])) {
                    continue;
                }
                $result['bundle_selections'][$key] = $option['bundle_selections'];
                unset($option['bundle_selections']);
                $result['bundle_options'][$key] = $option;
            }
            if ($result['bundle_selections'] && !$compositeReadonly) {
                $cedproduct->setBundleSelectionsData($result['bundle_selections']);
            }

            if ($result['bundle_options'] && !$compositeReadonly) {
                $cedproduct->setBundleOptionsData($result['bundle_options']);
            }

            $this->processBundleOptionsData($cedproduct);
            $this->processDynamicOptionsData($cedproduct);
        } elseif (!$compositeReadonly) {
            $extension = $cedproduct->getExtensionAttributes();
            $extension->setBundleProductOptions([]);
            $cedproduct->setExtensionAttributes($extension);
        }

        $affectProductSelections = (bool)$this->cedrequest->getPost('affect_bundle_product_selections');
        $cedproduct->setCanSaveBundleSelections($affectProductSelections && !$compositeReadonly);
        return $cedproduct;
    }

    /**
     * @param \Magento\Catalog\Model\Product $cedproduct
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processBundleOptionsData(\Magento\Catalog\Model\Product $cedproduct)
    {
        $bundleOptionsData = $cedproduct->getBundleOptionsData();
        if (!$bundleOptionsData) {
            return;
        }
        $options = [];
        foreach ($bundleOptionsData as $key => $optionData) {
            if (!empty($optionData['delete'])) {
                continue;
            }

            $option = $this->optionFactory->create(['data' => $optionData]);
            $option->setSku($cedproduct->getSku());

            $links = [];
            $bundleLinks = $cedproduct->getBundleSelectionsData();
            if (empty($bundleLinks[$key])) {
                continue;
            }

            foreach ($bundleLinks[$key] as $linkData) {
                if (!empty($linkData['delete'])) {
                    continue;
                }
                if (!empty($linkData['selection_id'])) {
                    $linkData['id'] = $linkData['selection_id'];
                }
                $links[] = $this->buildLink($cedproduct, $linkData);
            }
            $option->setProductLinks($links);
            $options[] = $option;
        }

        $extension = $cedproduct->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $cedproduct->setExtensionAttributes($extension);
    }

    /**
     * @param \Magento\Catalog\Model\Product $cedproduct
     * @return void
     */
    protected function processDynamicOptionsData(\Magento\Catalog\Model\Product $cedproduct)
    {
        if ((int)$cedproduct->getPriceType() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            return;
        }

        if ($cedproduct->getOptionsReadonly()) {
            return;
        }
        $cedproduct->setCanSaveCustomOptions(true);
        $customOptions = $cedproduct->getProductOptions();
        if (!$customOptions) {
            return;
        }
        foreach (array_keys($customOptions) as $key) {
            $customOptions[$key]['is_delete'] = 1;
        }
        $newOptions = $cedproduct->getOptions();
        foreach ($customOptions as $customOptionData) {
            if ((bool)$customOptionData['is_delete']) {
                continue;
            }
            $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
            $customOption->setProductSku($cedproduct->getSku());
            $newOptions[] = $customOption;
        }
        $cedproduct->setOptions($newOptions);
    }

    /**
     * @param \Magento\Catalog\Model\Product $cedproduct
     * @param array $linkData
     *
     * @return \Magento\Bundle\Api\Data\LinkInterface
     */
    private function buildLink(
        \Magento\Catalog\Model\Product $cedproduct,
        array $linkData
    ) {
        $link = $this->linkFactory->create(['data' => $linkData]);

        if ((int)$cedproduct->getPriceType() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            if (array_key_exists('selection_price_value', $linkData)) {
                $link->setPrice($linkData['selection_price_value']);
            }
            if (array_key_exists('selection_price_type', $linkData)) {
                $link->setPriceType($linkData['selection_price_type']);
            }
        }

        $linkProduct = $this->productRepository->getById($linkData['product_id']);
        $link->setSku($linkProduct->getSku());
        $link->setQty($linkData['selection_qty']);

        if (array_key_exists('selection_can_change_qty', $linkData)) {
            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
        }

        return $link;
    }
}
