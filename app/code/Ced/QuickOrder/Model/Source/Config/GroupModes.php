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
 * @package     Ced_QuickOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\QuickOrder\Model\Source\Config;

class GroupModes implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * GroupModes constructor.
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     */

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup      
        )
    {        
         $this->_customerGroup = $customerGroup; 
    }

    /**
     * @return mixed
     */
    public function toOptionArray()
    {
      $customerGroups =  $this->getCustomerGroups();
      return $customerGroups;
    }

    /**
     * @return mixed
     */
    public function getCustomerGroups() {
            $customerGroups = $this->_customerGroup->toOptionArray();
            return $customerGroups;
        }
}
