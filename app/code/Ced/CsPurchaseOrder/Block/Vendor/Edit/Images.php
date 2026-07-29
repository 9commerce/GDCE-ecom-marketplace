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
 * @package     Ced_CsPurchaseOrder
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsPurchaseOrder\Block\Vendor\Edit;

use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

/**
 * Adminhtml summary rating stars
 */
class Images extends \Magento\Backend\Block\Template
{
    /**
     * Rating summary template name
     *
     * @var string
     */
    protected $_template = 'Ced_CsPurchaseOrder::purchaseorder/images.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    
    protected $_coreRegistry = null;

    /**
     * Rating resource option model
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory
     */
    protected $_votesFactory;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getPoImages(){
    	 return explode(',',$this->_coreRegistry->registry('porequest')->getImages());
    }
    public function getMediaDirectory()
    {
    	$mediaDirectory  = $this->_filesystem
    	->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    	$path = $mediaDirectory->getAbsolutePath('cspurchaseorder/images/'.$this->_coreRegistry->registry('porequest')->getCustomerId());
    	return $path;
    }
    public Function getImageSrc()
    {
    	$url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'cspurchaseorder/images/'.$this->_coreRegistry->registry('porequest')->getCustomerId().'/';
    	return $url;
    }
}
