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

namespace Ced\CsMarketplace\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlFactory;

/**
 * Class Vendor
 * @package Ced\CsMarketplace\Controller
 */
abstract class Vendor extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var array
     */
    public static $openActions = [
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'confirm',
        'confirmation',
        'approval',
        'approvalPost',
        'checkAvailability',
        'denied',
        'noRoute',
        'register'
    ];

    /**
     * @var bool
     */
    public $_allowedResource = true;

    /**
     *
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $aclHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    protected $vendor;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Vendor constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        $this->session = $customerSession;
        $this->urlModel = $urlFactory;
        $this->registry = $registry;
        $this->resultJsonFactory = $jsonFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->aclHelper = $aclHelper;
        $this->vendor = $vendor;
        $this->resultPageFactory = $resultPageFactory;

        if (!$this->registry->registry('vendorPanel'))
            $this->registry->register('vendorPanel', 1);

        if (!$this->registry->registry('vendor'))
            $this->registry->register('vendor', $this->session->getVendor());

        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Dispatch request
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }
        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^(' . implode('|', $this->getAllowedActions()) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->authenticate($this)) {
                if ($this->getRequest()->isAjax()) {
                    $loginUrl = $this->urlModel->create()
                        ->getUrl('csmarketplace/account/login', ['_secure' => $this->getRequest()->isSecure()]);

                    $ajaxResponse = [];
                    $ajaxResponse['ajaxExpired'] = true;
                    $ajaxResponse['ajaxRedirect'] = $loginUrl;
                    $this->getResponse()->setBody(json_encode($ajaxResponse));
                    $resultJson = $this->resultJsonFactory->create();
                    return $resultJson->setData($ajaxResponse);
                }

                $this->_actionFlag->set('', 'no-dispatch', true);
            } elseif (!$this->aclHelper->isEnabled()) {
                $resultRedirect->setPath('customer/account/');
                return $resultRedirect;
            } elseif (!$this->csmarketplaceHelper->authenticate($this->session->getCustomerId())) {
                $this->session->unsVendorId();
                $this->session->unsVendor();
                $resultRedirect->setPath('*/account/approval');
                return $resultRedirect;
            }
        } else {
            $this->session->setNoReferer(true);
        }
        $result = parent::dispatch($request);
        $this->_eventManager->dispatch(
            'ced_csmarketplace_predispatch_action', [
                'session' => $this->session,
            ]
        );
        $this->session->unsNoReferer(false);
        return $result;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
     */
    protected function getAllowedActions()
    {
        return self::$openActions;
    }

    /**
     * Authenticate controller action by login customer
     *
     * @param Vendor $action
     * @param null $loginUrl
     * @return bool
     */
    public function authenticate(Vendor $action, $loginUrl = null)
    {
        if (!$this->session->isLoggedIn()) {
            if ($action->getRequest()->isAjax()) {
                $this->session->setBeforeVendorAuthUrl($this->urlModel->create()
                    ->getUrl('*/vendor/', ['_secure' => true, '_current' => true]));
            } else {
                $oAuthUrl = $this->urlModel->create()->getUrl('*/*/*', ['_current' => true]);
                if (!preg_match('/' . preg_quote('csmarketplace') . '/i', $oAuthUrl)) {
                    $oAuthUrl = $this->urlModel->create()->getUrl('csmarketplace/vendor/index', ['_current' => true]);
                }
                $this->session->setBeforeVendorAuthUrl($oAuthUrl);
            }
            if ($loginUrl === null) {
                $url = 'csmarketplace/account/login';
                $loginUrl = $this->urlModel->create()->getUrl($url, ['_secure' => $action->getRequest()->isSecure()]);
            }
            if ($action->getRequest()->isAjax()) {
                $ajaxResponse = [];
                $ajaxResponse['ajaxExpired'] = true;
                $ajaxResponse['ajaxRedirect'] = $loginUrl;
                $action->getResponse()->setBody(json_encode($ajaxResponse));
                //$resultJson = $this->resultJsonFactory->create();
                //return $resultJson->setData($response);
            }
            $action->getResponse()->setRedirect($loginUrl);
            return false;
        }
        if ($this->session->isLoggedIn() && $this->csmarketplaceHelper->authenticate($this->session->getCustomerId())) {
            $vendor = $this->vendor->create()->loadByCustomerId($this->session->getCustomerId());
            if ($vendor && $vendor->getId()) {
                $this->session->setVendorId($vendor->getId());
                $this->session->setVendor($vendor->getData());

                if (!$this->registry->registry('vendor'))
                    $this->registry->register('vendor', $vendor->getData());

                $this->_eventManager->dispatch(
                    'ced_csmarketplace_vendor_authenticate_after', [
                        'session' => $this->session
                    ]
                );
            }
        }
        $this->_eventManager->dispatch(
            'ced_csmarketplace_vendor_acl_check', [
                'current' => $this,
                'action' => $action,
            ]
        );
        return $this->_allowedResource;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
    }

    /**
     *
     * Retrieve customer session model object
     *
     * @return Session
     */
    protected function _getSession()
    {
        return $this->session;
    }
}
