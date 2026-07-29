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
 * @package     Ced_AdvanceConfigurable
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\AdvanceConfigurable\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class Add
 * @package Ced\AdvanceConfigurable\Controller\Cart
 */
class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * Add constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $productRepository);

        $this->cart = $cart;
        $this->productFactory = $productFactory;
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $cart = $this->cart;
        try {
            foreach ($post['attr'] as $key => $value) {
                if ($value) {
                    $params = array(
                        'product' => $post['product'], // This would be $product->getId()
                        'qty' => $value,
                        'super_attribute' => array($post['super_attribute'] => $key)

                    );
                    $productobj = $this->productFactory->create()->load($post['product']);
                    if (!$productobj->getId()) {
                        $this->messageManager->addErrorMessage(__('Product Does Not Exist. Contact Administrator'));
                    }
                    //$cart->truncate();
                    $cart->addProduct($productobj, $params);
                }

            }
            $cart->save();
            if (!$cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $productobj->getName()
                );
                $this->messageManager->addSuccessMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }

    }
}
