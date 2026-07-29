<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsImportExport\Model\Import\Product\Type;

/**
 * Import entity virtual product type
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Virtual extends \Ced\CsImportExport\Model\Import\Product\Type\Simple
{
    /**
     * Type virtual product
     */
    const TYPE_VIRTUAL_PRODUCT = 'virtual';

    /**
     * Prepare attributes with default value for save.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     * @return array
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = parent::prepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue);
        $resultAttrs = array_merge($resultAttrs, $this->setWeightVirtualProduct($rowData));
        return $resultAttrs;
    }

    /**
     * Set weight is null if product is virtual
     *
     * @param array $rowData
     * @return array
     */
    protected function setWeightVirtualProduct(array $rowData)
    {
        $result = [];
        if ($rowData['product_type'] == self::TYPE_VIRTUAL_PRODUCT) {
            $result['weight'] = null;
        }
        return $result;
    }
}
