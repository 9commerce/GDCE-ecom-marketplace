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

namespace Ced\CsImportExport\Model\Import\Product;

/**
 * Class SkuProcessor
 * @package Ced\CsImportExport\Model\Import\Product
 */
class SkuProcessor
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $oldSkus;

    /**
     * Dry-runned products information from import file.
     *
     * [SKU] => array(
     *     'type_id'        => (string) product type
     *     'attr_set_id'    => (int) product attribute set ID
     *     'entity_id'      => (int) product ID (value for new products will be set after entity save)
     *     'attr_set_code'  => (string) attribute set code
     * )
     *
     * @var array
     */
    protected $newSkus;

    /**
     * @var array
     */
    protected $productTypeModels;

    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Product entity identifier field
     *
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * SkuProcessor constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->productFactory = $productFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param array $typeModels
     * @return $this
     */
    public function setTypeModels($typeModels)
    {
        $this->productTypeModels = $typeModels;
        return $this;
    }

    /**
     * Get old skus array.
     *
     * @return array
     */
    public function getOldSkus()
    {
        if (!$this->oldSkus) {
            $this->oldSkus = $this->_getSkus();
        }
        return $this->oldSkus;
    }

    /**
     * Reload old skus.
     *
     * @return $this
     */
    public function reloadOldSkus()
    {
        $this->oldSkus = $this->_getSkus();

        return $this;
    }

    /**
     * @param string $sku
     * @param array $data
     * @return $this
     */
    public function addNewSku($sku, $data)
    {
        $this->newSkus[$sku] = $data;
        return $this;
    }

    /**
     * @param string $sku
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function setNewSkuData($sku, $key, $data)
    {
        if (isset($this->newSkus[$sku])) {
            $this->newSkus[$sku][$key] = $data;
        }
        return $this;
    }

    /**
     * @param null|string $sku
     * @return array|null
     */
    public function getNewSku($sku = null)
    {
        if ($sku !== null) {
            return isset($this->newSkus[$sku]) ? $this->newSkus[$sku] : null;
        }
        return $this->newSkus;
    }

    /**
     * Get skus data.
     *
     * @return array
     */
    protected function _getSkus()
    {
        $oldSkus = [];
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        if ($this->getProductEntityLinkField() != $this->getProductIdentifierField()) {
            $columns[] = $this->getProductEntityLinkField();
        }
        foreach ($this->productFactory->create()->getProductEntitiesInfo($columns) as $info) {
            $typeId = $info['type_id'];
            $sku = $info['sku'];
            $oldSkus[$sku] = [
                'type_id' => $typeId,
                'attr_set_id' => $info['attribute_set_id'],
                'entity_id' => $info['entity_id'],
                'supported_type' => isset($this->productTypeModels[$typeId]),
                $this->getProductEntityLinkField() => $info[$this->getProductEntityLinkField()],
            ];
        }
        return $oldSkus;
    }

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        return $this->metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }
}
