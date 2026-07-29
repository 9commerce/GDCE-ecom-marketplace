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

namespace Ced\CsMarketplace\Block\Vproducts;

use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\Vproducts;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreFactory;
use Magento\Tax\Model\ClassModelFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Locale\Format;


/**
 * Class Edit
 * @package Ced\CsMarketplace\Block\Vproducts
 */
class Edit extends AbstractBlock
{

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var Vproducts
     */
    protected $vproducts;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ClassModelFactory
     */
    protected $classModelFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Http
     */
    protected $httpRequest;

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * Edit constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Vproducts $vproducts
     * @param StoreFactory $storeFactory
     * @param ProductFactory $productFactory
     * @param Registry $registry
     * @param Type $type
     * @param PriceCurrencyInterface $priceCurrency
     * @param ClassModelFactory $classModelFactory
     * @param StockRegistryInterface $stockRegistry
     * @param ObjectManagerInterface $objectManager
     * @param Http $httpRequest
     * @param \Magento\Framework\Locale\Format $localeFormat
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Vproducts $vproducts,
        StoreFactory $storeFactory,
        ProductFactory $productFactory,
        Registry $registry,
        Type $type,
        PriceCurrencyInterface $priceCurrency,
        ClassModelFactory $classModelFactory,
        StockRegistryInterface $stockRegistry,
        ObjectManagerInterface $objectManager,
        Http $httpRequest,
        Format $localeFormat
    ) {
        $this->vproducts = $vproducts;
        $this->storeFactory = $storeFactory;
        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->type = $type;
        $this->priceCurrency = $priceCurrency;
        $this->classModelFactory = $classModelFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_objectManager = $objectManager;
        $this->httpRequest = $httpRequest;
        $this->localeFormat = $localeFormat;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $vendorId = $this->getVendorId();
        $id = $this->getRequest()->getParam('id');
        $status = 0;
        if ($id) {
            $vproductsCollection = $this->vproducts->getVendorProducts('', $vendorId, $id);
            $status = $vproductsCollection->getFirstItem()->getCheckStatus();
        }
        $storeId = 0;
        if ($this->getRequest()->getParam('store')) {
            $websiteId = $this->storeFactory->create()->load($this->getRequest()->getParam('store'))->getWebsiteId();
            if ($websiteId) {
                if (in_array($websiteId, $this->vproducts->getAllowedWebsiteIds())) {
                    $storeId = $this->getRequest()->getParam('store');
                }
            }
        }
        $product = $this->productFactory->create()->setStoreId($storeId);
        if ($id) {
            $product = $product->load($id);
        }
        $this->setVproduct($product);

        $registry = $this->registry;
        $registry->register('current_product', $product);

        $this->setCheckStatus($status);
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getDeleteUrl($product)
    {
        return $this->getUrl(
            '*/*/delete',
            ['id' => $product->getId(), '_secure' => true, '_nosid' => true]
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if($this->httpRequest->getActionName() == 'new'){
            return $this->getUrl(
                '*/*/new',
                ['_secure' => true, '_nosid' => true]
            );
        }
        elseif($this->httpRequest->getActionName() == 'edit'){
            return $this->getUrl(
                '*/*/index',
                ['_secure' => true, '_nosid' => true]
            );
        }

    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getDownloadableProductLinks($_product)
    {
        return $this->type->getLinks($_product);
    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getDownloadableHasLinks($_product)
    {
        return $this->type->hasLinks($_product);
    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getDownloadableProductSamples($_product)
    {
        return $this->type->getSamples($_product);
    }

    /**
     * @return PriceCurrencyInterface
     */
    public function getPriceCurrencyInterface()
    {
        return $this->priceCurrency;
    }

    /**
     * @param $_product
     * @return mixed
     */
    public function getDownloadableHasSamples($_product)
    {
        return $this->type->hasSamples($_product);
    }

    /**
     * @return mixed
     */
    public function getTaxModelCollection()
    {
        $collection = $this->classModelFactory->create()
            ->getCollection()
            ->addFieldToFilter('class_type', ['eq' => 'PRODUCT']);

        return $collection;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function createBlock($class)
    {
        return $this->_objectManager->get($class);
    }

    /**
     * @param $price
     * @return string
     */
    public function getFormattedPrice($price){
        $format= $this->localeFormat->getPriceFormat();
        $value = number_format($price??0.00, $format['precision'], $format['decimalSymbol'], $format['groupSymbol']);
        return $value;

    }
}
