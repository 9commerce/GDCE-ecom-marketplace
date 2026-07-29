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

namespace Ced\CsPurchaseOrder\Controller\Index;

class Getchildcategories extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
	protected $_categoryFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
	public $resultJsonFactory;

    /**
     * Getchildcategories constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
    	 \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
    	parent::__construct($context);
    	 $this->_categoryFactory = $categoryFactory;
    	 $this->resultJsonFactory = $resultJsonFactory;
    }
 
    public function execute()
    {
    
		if($this->getRequest()->isPost() && $data = $this->getRequest()->getParams()) {
			$id = $data['id'];
			$level = $data['level'] ;
			$level++;
			$subCategories = array();
			$category = $this->_categoryFactory->create()->load($id);
			if($category->getId()){
				$html='';
				//$html .= '<select id="select_category_'.$level.' onchange = ""('.$level.') ">';
				$children = $this->_categoryFactory->create()->getCategories($category->getId());
			  
				//$html .= '<select id="select_category_'.$level.'" onchange="getChildrenCategory('.$level.')" size="'.count($children).'" >';
				$count = 0;
				foreach ($children as $child){
					$child->getId();
					$html .= '<option value="'.$child->getId().'">'.$child->getName().'</option>';
					$count++;
				}
				$select  = '<li id="li_category_'.$level.'" class="wide ced-col-select-cat"><div class="input-box">';
				$select .= '<select id="select_category_'.$level.'" onchange="getChildrenCategory('.$level.')" size="6" >';
				$select .= $html;
				$select .='</select></div></li>';
			}
			if($count == 0){
				$subCategories['message'] = 'error';
			} else {
				$subCategories['message'] = 'success';
			}
			$subCategories['html'] = $select;
			$subCategories = json_encode($subCategories);
//			echo $subCategories;
            $result = $this->resultJsonFactory->create();
            return $result->setData($subCategories);
		}
		
    }
     
    
    
    
}
