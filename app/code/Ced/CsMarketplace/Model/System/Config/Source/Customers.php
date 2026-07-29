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

namespace Ced\CsMarketplace\Model\System\Config\Source;

/**
 * Class Customers
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class Customers extends AbstractBlock
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * Customers constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve Option values array
     *
     * @param bool $selected
     * @return array
     */
    public function toOptionArray($selected = false)
    {
        $options = [];
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('customer_entity');
        if ($selected) {
            $select = $connection->select()
                ->from($table)
                ->where('entity_id = ?', $selected);
            $customers = $connection->fetchAll($select);
        } else {
            $customer_id = $this->request->getParam('customer_id');

            $select = $connection->select()
                ->from($table)
                ->where('entity_id = ?', $customer_id);
            $customers = $connection->fetchAll($select);
        }

        foreach ($customers as $customer) {
            $options[] = [
                'value' => $customer['entity_id'],
                'label' => $customer['firstname'] . " (" . $customer['email'] . ")"
            ];
        }
        return $options;
    }
}
