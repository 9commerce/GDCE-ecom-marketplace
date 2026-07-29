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

namespace Ced\QuickOrder\Controller\Index;


use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;

class Showresult extends \Magento\Framework\App\Action\Action
{
	/**
	 * 
	 * @var \Magento\Framework\Registry
	 */
	public $_coreRegistry=null;

	/**
	 * 
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	public $resultPageFactory;

	/**
	 * 
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	public $resultJsonFactory;

    /**
     * Showresult constructor.
     * @param Context $context
     * @param Registry $registry
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
	public function __construct(
		Context $context,
		Registry $registry,
		JsonFactory $resultJsonFactory,
		PageFactory $resultPageFactory
	){
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		$this->resultJsonFactory = $resultJsonFactory;
		parent:: __construct($context);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Magento\Framework\App\ActionInterface::execute()
	 */
    public function execute()
    {
      	$query = $this->getRequest()->getParam('query');
      	$hiddenTrId = $this->getRequest()->getParam('hiddenTrId');
      	$this->_coreRegistry->register('query',$query);
      	$this->_coreRegistry->register('hiddenTrId',$hiddenTrId);
      	$response = $this->resultPageFactory->create(true)->getLayout()
      		->createBlock('Ced\QuickOrder\Block\QuickOrder\Result')
      		->setName('quickorder_query_result')
      		->setTemplate('Ced_QuickOrder::view/result.phtml')->toHtml();
      	$resultJson = $this->resultJsonFactory->create();
      	return $resultJson->setData($response);
    }
}
