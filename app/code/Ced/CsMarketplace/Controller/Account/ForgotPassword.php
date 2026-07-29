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

namespace Ced\CsMarketplace\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ForgotPassword
 * @package Ced\CsMarketplace\Controller\Account
 */
class ForgotPassword extends \Magento\Customer\Controller\Account\ForgotPassword
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * ForgotPassword constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_registry = $registry;

        if (!$this->_registry->registry('vendorPanel'))
            $this->_registry->register('vendorPanel', 1);

        parent::__construct($context, $customerSession, $resultPageFactory);
    }

    /**
     * Dispatch request
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $result = parent::dispatch($request);
        $this->_eventManager->dispatch(
            'ced_csmarketplace_predispatch_action', [
                'session' => $this->session,
            ]
        );
        return $result;
    }
}
