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
 * @category    Ced
 * @package     Ced_Gst
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsGst\Model\Attribute\Source;
 
class Region extends \Magento\Framework\Model\AbstractModel {

    protected $_options;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
	\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
	
	public function getAllOptions()
    {
    	if (is_null($this->_options)) {
    		$regionCollection = $this->regionCollectionFactory->create()->addFieldToFilter('country_id','IN');
    		$this->_options = array(
    				'-1' => __('Please select region'));
    		foreach ($regionCollection as $value) {
    			$this->_options[] = array(
    					'label' => $value->getDefaultName(),
    					'value' => $value->getRegionId()
    			);
    		}
    	}
    	return $this->_options;
    }

	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
}
