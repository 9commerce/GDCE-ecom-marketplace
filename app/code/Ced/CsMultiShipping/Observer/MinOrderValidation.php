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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Observer;

use Ced\CsMultiShipping\Model\Shipping;
use Magento\Framework\Event\ObserverInterface;
use Ced\CsMultiShipping\Model\ValidationRules\VendorMinimumAmountValidationRule;
class MinOrderValidation implements ObserverInterface
{
    /**
     * @param \Magento\Checkout\Model\Cart $cart,
     * @param VendorMinimumAmountValidationRule $minOrderValidation,
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect,
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        VendorMinimumAmountValidationRule $minOrderValidation,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
         $this->redirect = $redirect;
         $this->cart = $cart;
         $this->minOrderValidation = $minOrderValidation;
         $this->messageManager = $messageManager;
    }

    /**
     * Check Min order amount for vendor
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->cart->getQuote();
        $this->minOrderValidation->validate($quote);
        if($quote->getHasError()){
            $this->messageManager->addErrorMessage($quote->getMessage());
            $controller = $observer->getControllerAction();
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart/index');
        }
        return $this;
    }
}
