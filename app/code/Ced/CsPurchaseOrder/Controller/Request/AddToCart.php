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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Request;

/**
 * Class AddToCart
 * @package Ced\CsPurchaseOrder\Controller\Request
 */
class AddToCart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutsession;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * AddToCart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutsession
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutsession,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    )
    {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->checkoutsession = $checkoutsession;
        $this->cartFactory = $cartFactory;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->sessionFactory->create()->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        if ($this->getRequest()->getParam('requestid') && $this->getRequest()->getParam('product_id')) {

            $product_id = $this->getRequest()->getParam('product_id');
            $quote = $this->purchaseorderFactory->create()->load($this->getRequest()->getParam('requestid'));

            $product = $this->productFactory->create()->load($product_id);
            $params = [
                'product' => $product_id, // This would be $product->getId()
                'qty' => $quote->getNegotiatedFinalQty(),
                'price' => $quote->getNegotiatedFinalPrice()
            ];
            $quote_items = $this->checkoutsession->getQuote()->getAllItems();

            if ($quote_items) {
                foreach ($quote_items as $quote_item) {
                    if ($quote_item->getProductId() == $product_id) {
                        $this->messageManager->addErrorMessage(__('Please remove the quote product from that cart'));
                        return $this->_redirect('*/request/view');
                    }
                }
            }
            $this->checkoutsession->setParams($params);

            try {
                $cart = $this->cartFactory->create();
                $cart->addProduct($product, $params);
                $cart->save();

                $item_id = '';
                $quote_items = $this->checkoutsession->getQuote()->getAllItems();
                foreach ($quote_items as $quote_item) {
                    if ($quote_item->getProductId() == $this->getRequest()->getParam('product_id')) {
                        $item_id = $quote_item->getItemId();
                        break;
                    }
                }
                if ($item_id) {
                    $quote = $this->purchaseorderFactory->create()->load($this->getRequest()->getParam('requestid'));
                    $quote->setQuoteItemId($item_id)->save();
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                return $this->_redirect('*/request/view');
            }

            return $this->_redirect('checkout');
        }
        return $this->_redirect('*/request/view');
    }
}
