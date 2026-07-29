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

namespace Ced\CsMarketplace\Model\ResourceModel\Notification;

/**
 * Class Collection
 * @package Ced\CsMarketplace\Model\ResourceModel\Notification
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @param $dataToUpdate
     * @param $condition
     */
    public function updateRecords($dataToUpdate, $condition)
    {
        $this->getConnection()->update($this->getTable('ced_csmarketplace_notification'), $dataToUpdate, $condition);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\Notification', 'Ced\CsMarketplace\Model\ResourceModel\Notification');
    }
}
