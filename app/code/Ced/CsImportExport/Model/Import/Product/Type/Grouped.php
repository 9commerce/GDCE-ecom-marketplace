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
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Model\Import\Product\Type;

use Ced\CsImportExport\Model\Import\Product;
use Ced\CsImportExport\Model\Import;

/**
 * Class Grouped
 * @package Ced\CsImportExport\Model\Import\Product\Type
 */
class Grouped extends \Ced\CsImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Default delimiter for sku and qty.
     */
    const SKU_QTY_DELIMITER = '=';

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_specialAttributes = ['_associated_sku', '_associated_default_qty', '_associated_position'];

    /**
     * @var Grouped\Links
     */
    protected $links;

    /**
     * Product entity identifier field
     *
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * Grouped constructor.
     * @param Grouped\Links $links
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Grouped\Links $links,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        array $params
    ) {
        parent::__construct($prodAttrColFac, $resource, $metadataPool, $params);

        $this->links = $links;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $attributes = $this->links->getAttributes();
        $productData = [];
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData = [
                'product_ids' => [],
                'attr_product_ids' => [],
                'position' => [],
                'qty' => [],
                'relation' => []
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if ($this->_type != $rowData[Product::COL_TYPE]) {
                    continue;
                }
                $associatedSkusQty = isset($rowData['associated_skus']) ? $rowData['associated_skus'] : null;
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum) || empty($associatedSkusQty)) {
                    continue;
                }
                $associatedSkusAndQtyPairs = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $associatedSkusQty);
                $position = 0;
                foreach ($associatedSkusAndQtyPairs as $associatedSkuAndQty) {
                    ++$position;
                    $associatedSkuAndQty = explode(self::SKU_QTY_DELIMITER, $associatedSkuAndQty);
                    $associatedSku = isset($associatedSkuAndQty[0]) ? trim($associatedSkuAndQty[0]) : null;
                    if (isset($newSku[$associatedSku])) {
                        $linkedProductId = $newSku[$associatedSku][$this->getProductEntityIdentifierField()];
                    } elseif (isset($oldSku[$associatedSku])) {
                        $linkedProductId = $oldSku[$associatedSku][$this->getProductEntityIdentifierField()];
                    } else {
                        continue;
                    }
                    $scope = $this->_entityModel->getRowScope($rowData);
                    if (Product::SCOPE_DEFAULT == $scope) {
                        $productData = $newSku[$rowData[Product::COL_SKU]];
                    } else {
                        $colAttrSet = Product::COL_ATTR_SET;
                        $rowData[$colAttrSet] = $productData['attr_set_code'];
                        $rowData[Product::COL_TYPE] = $productData['type_id'];
                    }
                    $productId = $productData[$this->getProductEntityLinkField()];

                    $linksData['product_ids'][$productId] = true;
                    $linksData['relation'][] = ['parent_id' => $productId, 'child_id' => $linkedProductId];
                    $qty = empty($associatedSkuAndQty[1]) ? 0 : trim($associatedSkuAndQty[1]);
                    $linksData['attr_product_ids'][$productId] = true;
                    $linksData['position']["{$productId} {$linkedProductId}"] = [
                        'product_link_attribute_id' => $attributes['position']['id'],
                        'value' => $position
                    ];
                    if ($qty) {
                        $linksData['attr_product_ids'][$productId] = true;
                        $linksData['qty']["{$productId} {$linkedProductId}"] = [
                            'product_link_attribute_id' => $attributes['qty']['id'],
                            'value' => $qty
                        ];
                    }
                }
            }
            $this->links->saveLinksData($linksData);
        }
        return $this;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductEntityIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
