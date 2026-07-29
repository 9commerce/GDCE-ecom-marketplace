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
use Magento\Framework\View\Result\PageFactory;

class Configurable extends \Magento\Framework\App\Action\Action
{
  

    /**
     * @var Registry
     */
  public $_coreRegistry;

    /**
     * @var JsonFactory
     */
  public $resultJsonFactory;

    /**
     * @var PageFactory
     */
  public $resultPageFactory;

    /**
     * Configurable constructor.
     * @param Context $context
     * @param Registry $registry
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param array $data
     */
	
	 public function __construct(
	      Context $context,
        Registry $registry,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        array $data = []
	 ){
      $this->_coreRegistry = $registry;
      $this->resultJsonFactory = $resultJsonFactory;
      $this->resultPageFactory = $resultPageFactory;
	   parent:: __construct($context);
	}

    /**
     * @return mixed
     */
    public function execute()
    {
      $productId = $this->getRequest()->getParam('configurableProductId');
      $trId = $this->getRequest()->getParam('trId');
      $this->_coreRegistry->register('productId',$productId);
      $this->_coreRegistry->register('trId',$trId);
      $response = $this->resultPageFactory->create(true)->getLayout()
      ->createBlock('Ced\QuickOrder\Block\QuickOrder\Configurable')
      ->setName('quickorder_index_configurable')
      ->setTemplate('Ced_QuickOrder::view/configurable.phtml')->toHtml();
      $resultJson = $this->resultJsonFactory->create();
      return $resultJson->setData($response);

    }
    
}
