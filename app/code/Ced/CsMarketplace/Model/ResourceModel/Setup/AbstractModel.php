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

namespace Ced\CsMarketplace\Model\ResourceModel\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractModel
 * @package Ced\CsMarketplace\Model\ResourceModel\Setup
 */
class AbstractModel extends \Magento\Eav\Model\Entity
{

    /**
     * Create entity tables
     * @param $baseTableName
     * @param array $options
     * @return AbstractModel
     * @throws LocalizedException
     */
    public function createEntityTables($baseTableName, array $options = [])
    {
        return $this->createEntityTablesAbove16($baseTableName, $options);
    }

    /**
     * @param $baseTableName
     * @param array $options
     * @return $this
     * @throws LocalizedException
     * @throws \Zend_Db_Exception
     */
    public function createEntityTablesAbove16($baseTableName, array $options = [])
    {
        $isNoCreateMainTable = $this->_getValue($options, 'no-main', false);
        $isNoDefaultTypes = $this->_getValue($options, 'no-default-types', false);
        $customTypes = $this->_getValue($options, 'types', []);
        $tables = [];
        $connection = $this->getConnection();

        if (!$isNoCreateMainTable) {
            /* Create table main eav table */
            $mainTable = $connection
                ->newTable($this->getTable($baseTableName))
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Entity Id'
                )
                ->addColumn(
                    'entity_type_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Entity Type Id'
                )
                ->addColumn(
                    'attribute_set_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Attribute Set Id'
                )
                ->addColumn(
                    'increment_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false,
                        'default' => '',
                    ],
                    'Increment Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Updated At'
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '1',
                    ],
                    'Defines Is Entity Active'
                )
                ->addIndex(
                    $this->getIdxName($baseTableName, ['entity_type_id']),
                    array('entity_type_id')
                )
                ->addIndex(
                    $this->getIdxName($baseTableName, ['store_id']),
                    ['store_id']
                )
                ->addForeignKey(
                    $this->getFkName(
                        $baseTableName,
                        'entity_type_id',
                        'eav_entity_type',
                        'entity_type_id'
                    ),
                    'entity_type_id',
                    $this->getTable('eav_entity_type'),
                    'entity_type_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $this->getFkName($baseTableName,
                        'store_id',
                        'store',
                        'store_id'
                    ),
                    'store_id',
                    $this->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->setComment('Eav Entity Main Table');

            $tables[$this->getTable($baseTableName)] = $mainTable;
        }

        $types = [];
        if (!$isNoDefaultTypes) {
            $types = [
                'datetime' => [Table::TYPE_DATETIME, null],
                'decimal' => [Table::TYPE_DECIMAL, '12,4'],
                'int' => [Table::TYPE_INTEGER, null],
                'text' => [Table::TYPE_TEXT, '64k'],
                'varchar' => [Table::TYPE_TEXT, '255'],
                'char' => [Table::TYPE_TEXT, '255']
            ];
        }

        if (!empty($customTypes)) {
            foreach ($customTypes as $type => $fieldType) {
                if (count($fieldType) != 2) {
                    throw new LocalizedException('Magento_Eav' .
                        __('Wrong type definition for %1', $type));
                }
                $types[$type] = $fieldType;
            }
        }

        /**
         * Create table array($baseTableName, $type)
         */
        foreach ($types as $type => $fieldType) {
            $eavTableName = array($baseTableName, $type);

            $eavTable = $connection->newTable($this->getTable($eavTableName));
            $eavTable
                ->addColumn(
                    'value_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Value Id'
                )
                ->addColumn(
                    'entity_type_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Entity Type Id'
                )
                ->addColumn(
                    'attribute_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Attribute Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                    ],
                    'Entity Id'
                )
                ->addColumn(
                    'value',
                    $fieldType[0],
                    $fieldType[1],
                    [
                        'nullable' => false,
                    ],
                    'Attribute Value'
                )
                ->addIndex(
                    $this->getIdxName($eavTableName, ['entity_type_id']),
                    ['entity_type_id']
                )
                ->addIndex(
                    $this->getIdxName($eavTableName, ['attribute_id']),
                    ['attribute_id']
                )
                ->addIndex(
                    $this->getIdxName($eavTableName, ['store_id']),
                    ['store_id']
                )
                ->addIndex(
                    $this->getIdxName($eavTableName, ['entity_id']),
                    ['entity_id']
                );
            if ($type !== 'text') {
                $eavTable->addIndex(
                    $this->getIdxName($eavTableName, ['attribute_id', 'value']),
                    ['attribute_id', 'value']
                );
                $eavTable->addIndex(
                    $this->getIdxName($eavTableName, ['entity_type_id', 'value']),
                    ['entity_type_id', 'value']
                );
            }

            $eavTable
                ->addForeignKey(
                    $this->getFkName(
                        $eavTableName,
                        'entity_id',
                        $baseTableName,
                        'entity_id'
                    ),
                    'entity_id',
                    $this->getTable($baseTableName),
                    'entity_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $this->getFkName(
                        $eavTableName,
                        'entity_type_id',
                        'eav_entity_type',
                        'entity_type_id'
                    ),
                    'entity_type_id',
                    $this->getTable('eav_entity_type'),
                    'entity_type_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $this->getFkName(
                        $eavTableName,
                        'store_id',
                        'store',
                        'store_id'
                    ),
                    'store_id',
                    $this->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE,
                    Table::ACTION_CASCADE
                )
                ->setComment('Eav Entity Value Table');

            $tables[$this->getTable($eavTableName)] = $eavTable;
        }

        try {
            foreach ($tables as $tableName => $table) {
                $connection->createTable($table);
            }
        } catch (\Exception $e) {
            throw new LocalizedException('Magento_Eav' .
                __('Can\'t create table: %1', $tableName));
        }

        return $this;
    }
}
