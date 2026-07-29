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

namespace Ced\QuickOrder\Controller\Csv;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\File\Csv;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Index extends \Magento\Framework\App\Action\Action {

    /**
     * @var CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManagerInterface;

    /**
     * @var CurrencyFactory
     */
    public $currencyStoreFactory;

    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var StockItemRepository
     */
    public $stockItemRepository;

    /**
     * @var ProductRepository
     */
    public $productRepository;

    /**
     * @var Csv
     */
    public $csvProcessor;

    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var Product
     */
    public $catalogProduct;
    /**
     * Index constructor.
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param CurrencyFactory $currencyStoreFactory
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepository $stockItemRepository
     * @param ProductRepository $productRepository
     * @param Csv $csvProcessor
     * @param JsonFactory $resultJsonFactory
     * @param Product $catalogProduct
     */
    public function __construct( 
        Context $context,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManagerInterface,
        CurrencyFactory $currencyStoreFactory,
        StockRegistryInterface $stockRegistry,
        StockItemRepository $stockItemRepository,
        ProductRepository $productRepository,
        Csv $csvProcessor,
        JsonFactory $resultJsonFactory,
        Product $catalogProduct,
        PriceCurrencyInterface $priceCurrencyObject
    )
    {
         $this->productCollectionFactory = $productCollectionFactory;
         $this->storeManagerInterface = $storeManagerInterface;
         $this->currencyStoreFactory = $currencyStoreFactory;
         $this->stockRegistry = $stockRegistry;
         $this->stockItemRepository = $stockItemRepository;
         $this->productRepository = $productRepository;
         $this->csvProcessor = $csvProcessor;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->catalogProduct = $catalogProduct;
         $this->priceCurrencyObject = $priceCurrencyObject;
         parent:: __construct($context);
         
    }

    /**
     * @return mixed
     */
        public function execute(){
            $file = $this->getRequest()->getFiles();
            // print_r($file);die(get_class($this));
            return $this->importFromCsvFile($file);
        }

        public function importFromCsvFile($file)
        {
         if (!isset($file['file']['tmp_name'])) {
             throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
         }
         
         $productData = $this->csvProcessor->getData($file['file']['tmp_name']);
         if(empty($productData)){
            $result = $this->resultJsonFactory->create();
            return $result->setData('blank_csv');
         }
         if(isset($productData[0][0]) && isset($productData[0][1])){
               if($productData[0][0]=='sku' && $productData[0][1]=='qty'){
                       unset($productData[0]);
                       $skuArray = [];
                       $collectionQtySkuArray = [];
                       $allSkuValuesCollection = [];
                       $getcountArray = [];
                       $qtySkuArray = [];
                          foreach ($productData as $rowIndex => $dataRow) {
                                                      
                              if(isset($dataRow[0]) &&  isset($dataRow[1])){
                                 if(preg_match('/^\d+$/', $dataRow[1]) && 
                                    is_numeric($dataRow[1]) 
                                    && $dataRow[1]>0 && $dataRow
                                ){
                                  if(isset($qtySkuArray[$dataRow[0]])){
                                     $qty = $qtySkuArray[$dataRow[0]]['qty'];
                                     $qtySkuArray[$dataRow[0]] = [
                                        'sku' => $dataRow[0], 
                                        'qty' => $dataRow[1]+$qty
                                    ];
                                  }else{
                                      $qtySkuArray[$dataRow[0]] = [
                                        'sku' => $dataRow[0], 
                                        'qty' => $dataRow[1]
                                      ];
                                  }  
                                  array_push($skuArray, $dataRow[0]); 
                                }
                              }
                                if(isset($dataRow[0]) && $dataRow[0]) {
                                  array_push($allSkuValuesCollection, strtoupper($dataRow[0]));    
                                }
                          }

                          $productCollection = $this->productCollectionFactory;
                          $collection = $productCollection->create()
                              ->addAttributeToSelect('*');
                          $collection
                                      ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
                                      ->joinAttribute('visibility', 'catalog_product/visibility', 
                                        'entity_id', null, 'inner')
                                      ->addStoreFilter($this->getStoreId())
                                      ->addAttributeToFilter(
                                        'status',
                                        \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                                      ->addAttributeToFilter('visibility',4)
                                      ->addAttributeToFilter('sku', array( 'in' => $skuArray ))
                                      ->addAttributeToFilter([
                                           ['attribute'=>'type_id','eq'=>'simple'],
                                           ['attribute'=>'type_id','eq'=>'configurable'],
                                           ['attribute'=>'type_id','eq'=>'virtual'],
                               ]);
                              $collectionArray = [];
                              $importedSku = [];
                              foreach ($collection as $key => $productvalue) {
                                    array_push($importedSku, strtoupper($productvalue->getSku()));
                                    $getSpecialPrice = $productvalue->getSpecialPrice();
                                    $customArray = [];
                                    $total = $this->stockRegistry->getStockItem($productvalue->getId());
                                    if(empty($getSpecialPrice)){
                                          $productPrice = $this->convertedPrice($productvalue->getPrice());
                                          $customArray['price'] =  $productPrice; 
                                    }  else{
                                          $productPrice = $this->convertedPrice($getSpecialPrice);
                                          $customArray['price'] =  $productPrice; 
                                    }
                                    $customArray['name'] =  $productvalue->getName();
                                    $customArray['symbol'] = $this->getCurrentCurrencyCode();
                                    $customArray['sku'] =  $productvalue->getSku();
                                    $customArray['product_id'] =  $productvalue->getId();
                                    $customArray['product_type'] = $productvalue->getTypeId();
                                   
                                    foreach ($qtySkuArray as $key2 => $value2) {
                                       if(strtoupper($key2) == strtoupper($productvalue->getSku())) {
                                            $customArray['qty'] = $value2['qty'];

                                       } 
                                    }
                                    if($productvalue->getTypeId()=='configurable'){
                                      $productCollection = $this->catalogProduct;
                                      $stockItemRepositary = $this->stockItemRepository;
                                      $product = $this->productRepository->getById($productvalue->getId());
                                      $config = $product->getTypeInstance(true);
                                      $childproduct = $config->getUsedProducts($product);
                                      $data = $product->getTypeInstance()->getConfigurableOptions($product);
                                      $final_array = [];
                                      ksort($data);
                                      foreach($data as $key => $attr){
                                          foreach($attr as $product){
                                              $pr = $this->productRepository->get($product['sku']);
                                              $productId = $productCollection->getIdBySku($product['sku']);
                                              $productStock = $stockItemRepositary->get($productId);
                                              $productQty = $productStock->getQty();
                                              $value_index = $product['value_index'];
                                              $final_array[$product['sku']][$key] = $value_index;
                                              $final_array[$product['sku']]['price'] = 
                                              $this->convertedPrice($pr->getPrice());
                                              $final_array[$product['sku']]['qty']  = $productQty;
                                          }
                                      }
                                        $i = 0;
                                        $attributesCombinationArray = [];
                                        $priceArray = [];
                                        $qtyArray = [];

                                      foreach ($final_array as $key => $value) {
                                          $str = '';
                                          $str_key = '';
                                        foreach ($value as $key1 => $value1) {
                                          if ($key1 != 'price' && $key1 != 'qty') {
                                            $str .= $key1.'-'.$value1.',';
                                            $str_key .= $key1.',';
                                          }
                                        }
                                          $str = rtrim($str,',');
                                          $attributesCombinationArray[$i] = $str;
                                          $str_key = rtrim($str_key,',');
                                          

                                        foreach ($value as $key1 => $value1) {
                                          if ($key1 == 'price') {
                                            $price = '';
                                            $price .= $value1;
                                          }
                                        }
                                          $priceArray[$i] = $price;

                                        foreach ($value as $key1 => $value1) {
                                          if ($key1 == 'qty') {
                                            $qty = '';
                                            $qty .= $value1;
                                          }
                                        }
                                          $qtyArray[$i] = $qty;
                                          
                                        $i++;   
                                      }

                                      $config_hidden_array_price = json_encode($priceArray);
                                      $config_hidden_encoded_val = json_encode($attributesCombinationArray);
                                      $config_hidden_encoded_qty = json_encode($qtyArray);
                                      $customArray['configurable_priceArray'] = $config_hidden_array_price;
                                      $customArray['config_hidden_encoded_val'] = $config_hidden_encoded_val;
                                      $customArray['qtyArray'] = $config_hidden_encoded_qty;
                                      $customArray['str_key'] = $str_key;
                                      $proRepo = $this->productRepository;
                                      $product = $proRepo->getById($productvalue->getId());
                                      $attributesArray = 
                                      $product->getTypeInstance()
                                              ->getConfigurableAttributesAsArray($product);
                                      $json_attributesArray = json_encode($attributesArray);
                                      $customArray['json_attributesArray'] = $json_attributesArray;
                                    }
                                   
                                    $total = $this->stockRegistry->getStockItem($productvalue->getId());
                                    $customArray['total_qty'] = (int)$total->getQty();
                                    array_push($collectionArray, $customArray);
                                    array_push($getcountArray, $productvalue->getSku());
                              }
                              $diiff_array = array_diff($allSkuValuesCollection, $importedSku);
                              $unimportedSku = implode(",", $diiff_array);
                              if($skuArray && empty($collection->getData())){
                                $skuArray = implode(",", $skuArray);
                                $result = $this->resultJsonFactory->create();
                                return $result->setData(['skuArrayText'=>'skuArray','skuArray'=> $skuArray]);
                              }
                               if(empty($collectionArray) && $unimportedSku){
                                 $result = $this->resultJsonFactory->create();
                                 return $result->setData([
                                    'null_collection_all_invalid'=>'null_collection_all_invalid',
                                    'unimportedSku'=> $unimportedSku]
                                    );
                              }
                              if(empty($collectionArray)){
                                 $result = $this->resultJsonFactory->create();
                                 return $result->setData('invalid_sku_qty');
                              }
                           $result = $this->resultJsonFactory->create();
                           return $result->setData([
                            'collectionArray' => $collectionArray,
                            'unimportedSku' => $unimportedSku,
                            'importedSku' => count($getcountArray),
                            'data_import' => 'data_import']);
            }
                else{
                    $result = $this->resultJsonFactory->create();
                    return $result->setData('improper_method');
                 }
            }           
                else{
                    $result = $this->resultJsonFactory->create();
                    return $result->setData('improper_method');
                }
         
         }

        /**
        * @return mixed
        */
         public function getWebsiteId()
         {
             return $this->storeManagerInterface->getStore()->getWebsiteId();
         }

        /**
        * @return mixed
        */
         public function getStoreId()
         {
             return $this->storeManagerInterface->getStore()->getId();
         }        
            /**
              * Get current store currency code
              *
              * @return string
              */
         public function getCurrentCurrencyCode()
            {
                $currentCurrencyCode = $this->storeManagerInterface->getStore()->getCurrentCurrencyCode();
                $currency = $this->currencyStoreFactory->create()->load($currentCurrencyCode);
                $currencySymbol = $currency->getCurrencySymbol();
                if($currencySymbol){
                     return $currencySymbol;
                }else{
                  return $currentCurrencyCode;
                }
                
            }

        public function convertedPrice($price){
               $store = $this->storeManagerInterface->getStore()->getStoreId();
               $currentCurrencyCode = $this->storeManagerInterface->getStore()->getCurrentCurrencyCode();
               $productPrice = $this->priceCurrencyObject->convert($price, $store, $currentCurrencyCode);
               return $productPrice;
        }

}