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

namespace Ced\CsImportExport\Model\Export\RowCustomizer;

use Ced\CsImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\ObjectManagerInterface;

class Composite implements RowCustomizerInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $customizers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $customizers
     */
    public function __construct(ObjectManagerInterface $objectManager, $customizers = [])
    {
        $this->objectManager = $objectManager;
        $this->customizers = $customizers;
    }

    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int[] $productIds
     * @return mixed|void
     */
    public function prepareData($collection, $productIds)
    {
        foreach ($this->customizers as $className) {
            $this->objectManager->get($className)->prepareData($collection, $productIds);
        }
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function addHeaderColumns($columns)
    {
        foreach ($this->customizers as $className) {
            $columns = $this->objectManager->get($className)->addHeaderColumns($columns);
        }
        return $columns;
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId)
    {
        foreach ($this->customizers as $className) {
            $dataRow = $this->objectManager->get($className)->addData($dataRow, $productId);
        }
        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array|mixed
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        foreach ($this->customizers as $className) {
            $additionalRowsCount = $this->objectManager->get(
                $className
            )->getAdditionalRowsCount(
                $additionalRowsCount,
                $productId
            );
        }
        return $additionalRowsCount;
    }
}
