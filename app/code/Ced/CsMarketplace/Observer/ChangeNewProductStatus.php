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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMarketplace\Observer;

use Ced\CsMarketplace\Model\Vproducts;
use Magento\Catalog\Model\Product\ActionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ChangeNewProductStatus
 * @package Ced\CsMarketplace\Observer
 */
Class ChangeNewProductStatus implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $_vproductsFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * ChangeNewProductStatus constructor.
     * @param ActionFactory $actionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_vproductsFactory = $vproductsFactory;
    }

    /**
     * Notify Customer Account status Change
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeManager = $this->_storeManager;
        $scopeConfig = $this->_scopeConfig;
        $confirmation = $scopeConfig->getValue(
            'ced_vproducts/general/confirmation',
            'store',
            $storeManager->getStore()->getCode()
        );
        $re_confirmation = $scopeConfig->getValue(
            'ced_csmarketplace/vproducts/re_confirmation',
            'store',
            $storeManager->getStore()->getCode()
        );

        $marketplaceProduct = $this->_vproductsFactory->create()->load($product->getId(), 'product_id');

        if ($marketplaceProduct && $marketplaceProduct->getId()) {
            $status = Status::STATUS_DISABLED;
            if ($marketplaceProduct->getCheckStatus() == Vproducts::APPROVED_STATUS) {
                $status = $product->getStatus();
            }
            if($confirmation  && $re_confirmation){
                $status =  Status::STATUS_DISABLED;
                $product->setStatus($status);
            }
            
        } else {
            $status = ($confirmation == true) ? Status::STATUS_DISABLED : Status::STATUS_ENABLED;
        }

        $this->actionFactory->create()->updateAttributes(
            [$product->getId()],
            ['status' => $status], 0
        );
    }
}
