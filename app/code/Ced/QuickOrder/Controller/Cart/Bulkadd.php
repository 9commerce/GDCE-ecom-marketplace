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

namespace Ced\QuickOrder\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;
use \Magento\Framework\DataObject;


class Bulkadd extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Cart
     */
   public $cart;

    /**
     * @var ProductRepositoryInterface
     */
   public $productRepository;

    /**
     * @var JsonFactory
     */
   public $resultJsonFactory;

    /**
     * Bulkadd constructor.
     * @param Context $context
     * @param Cart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param JsonFactory $resultJsonFactory
     */
	
	public function __construct(
	   Context $context,
	   Cart $cart,
	   ProductRepositoryInterface $productRepository,
	   JsonFactory $resultJsonFactory
	){
		$this->cart = $cart;
		$this->productRepository = $productRepository;
		$this->resultJsonFactory = $resultJsonFactory;
		parent:: __construct($context);
	}

    /**
     * @return mixed
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $post = $this->getRequest()->getPostValue();
      	$count =0;
      	try{
      		foreach($post['product'] as  $productData){
      			if(isset($productData['productid'])){
      				if($productData['productid']){
                     if ($productData['product_type']=='configurable') {
                        $config_hidden_key = explode(",", $productData['config_hidden_key']);
                        $config_hidden_val = explode(",", $productData['config_hidden_val']);
                        $array_combine = array_combine($config_hidden_key, $config_hidden_val);
                        $params = [
                          'product'=>$productData['productid'],
                          'qty'=>$productData['qty'],
                          'super_attribute'=>$array_combine
                        ];
                        $productobj =  $this->productRepository->getById($productData['productid']);
                        $this->cart->addProduct($productobj,$params);
                        $count++;
                     }
		      			else{
                        $params = ['product'=>$productData['productid'],'qty'=>$productData['qty']];
                        $productobj =  $this->productRepository->getById($productData['productid']);
                        $this->cart->addProduct($productobj,$params);
                        $count++;
                     }
      				}
      			}
      		}
      	$this->cart->save();

      	}catch(\Exception $e){
            $response['catch'] = true;
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
     	}

       $response['success'] = true;
       $resultJson = $this->resultJsonFactory->create();
       return $resultJson->setData($response);
       }  
    
}
