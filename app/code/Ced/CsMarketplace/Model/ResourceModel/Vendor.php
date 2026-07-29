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

namespace Ced\CsMarketplace\Model\ResourceModel;

/**
 * Class Vendor
 * @package Ced\CsMarketplace\Model\ResourceModel
 */
class Vendor extends \Magento\Eav\Model\Entity\AbstractEntity
{

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var
     */
    protected $groupObject;

    /**
     * Vendor constructor.
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Context $context,
        $groupobject =[],
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->groupObject = $groupobject;
        $this->setType('csmarketplace_vendor');
        $this->setConnection('csmarketplace_vendor_read', 'csmarketplace_vendor_write');
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->getTable('ced_csmarketplace_vendor');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $vendor
     * @return $this
     */
    public function deleteFromGroup(\Magento\Framework\Model\AbstractModel $vendor)
    {
        if ($this->moduleManager->isEnabled('Ced_CsGroup') && $this->getGroupObject()!=null) {
            if ($vendor->getId() <= 0) {
                return $this;
            }

            if (strlen($vendor->getGroup()) <= 0) {
                return $this;
            }
            $vendorGroup = $this->getGroupObject()->create()->loadByField('group_code', $vendor->getGroup());
            $dbh = $this->getConnection();

            $condition =
                "`{$this->getTable('ced_csgroup_vendor_group')}`.vendor_id = " . $dbh->quote($vendor->getId()) .
                " AND `{$this->getTable('ced_csgroup_vendor_group')}`.parent_id = " .
                $dbh->quote($vendorGroup->getGroupId());

            $dbh->delete($this->getTable('ced_csgroup_vendor_group'), $condition);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $vendor
     * @return array
     */
    public function groupVendorExists(\Magento\Framework\Model\AbstractModel $vendor)
    {
        if ($vendor->getId() > 0 && $this->moduleManager->isEnabled('Ced_CsGroup') && $this->getGroupObject()!=null) {
            $groupTable = $this->getTable('ced_csgroup_vendor_group');

            $vendorGroup = $this->getGroupObject()->create()->loadByField('group_code', $vendor->getGroup());

            $dbh = $this->getConnection();
            $select = $dbh->select()->from($groupTable)
                ->where("parent_id = {$vendorGroup->getGroupId()} AND vendor_id = {$vendor->getId()}");
            return $dbh->fetchCol($select);
        } else {
            return [];
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $vendor
     * @return $this
     */
    public function add(\Magento\Framework\Model\AbstractModel $vendor)
    {
        $dbh = $this->getConnection();
        $aGroups = $this->hasAssigned2Group($vendor);
        if (count($aGroups)) {
            foreach ($aGroups as $idx => $data) {
                $dbh->delete($this->getTable('ced_csgroup_vendor_group'), "group_id = {$data['group_id']}");
            }
        }

        if (strlen($vendor->getGroup()) > 0 && $this->getGroupObject()!=null) {
            $group = $this->getGroupObject()->create()->loadByField('group_code', $vendor->getGroup());
        } else {
            $group = new \Magento\Framework\DataObject();
            $group->setTreeLevel(0);
        }
        $dbh->insert(
            $this->getTable('ced_csgroup_vendor_group'), array(
                'parent_id' => $group->getId(),
                'tree_level' => ($group->getTreeLevel() + 1),
                'sort_order' => 0,
                'group_type' => 'U',
                'vendor_id' => $vendor->getId(),
                'group_code' => $vendor->getGroup(),
                'group_name' => $vendor->getName()
            )
        );

        return $this;
    }

    /**
     * @param $vendor
     * @return array|null
     */
    public function hasAssigned2Group($vendor)
    {
        if (is_numeric($vendor)) {
            $vendorId = $vendor;
        } else if ($vendor instanceof \Magento\Framework\Model\AbstractModel) {
            $vendorId = $vendor->getId();
        } else {
            return null;
        }

        if ($vendorId > 0) {
            $dbh = $this->getConnection();
            $select = $dbh->select();
            $select->from($this->getTable('ced_csgroup_vendor_group'))
                ->where("parent_id > 0 AND vendor_id = {$vendorId}");
            return $dbh->fetchAll($select);
        }
        return null;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $vendor
     * @return mixed
     */
    public function vendorExists(\Magento\Framework\Model\AbstractModel $vendor)
    {
        $vendorsTable = $this->getTable('ced_csmarketplace_vendor');
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from(['u' => $vendorsTable])
            ->where('u.vendor_id != ?', (int)$vendor->getId())
            ->where('u.vendorname = :vendorname OR u.email = :email');
        $row = $db->fetchRow(
            $select, [
                ':vendorname' => $vendor->getVendorname(),
                ':email' => $vendor->getVendorname(),
            ]
        );
        return $row;
    }

    /**
     * phpcs:disable Magento2.PHP.AutogeneratedClassNotInConstructor
     * @return object
     */
    public function getGroupObject(){
        if(empty($this->groupObject))
            return null;

        return $this->groupObject['csGroupObject'];
    }
}
