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

use Ced\CsMarketplace\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Approval
 * @package Ced\CsMarketplace\Controller\Account
 */
class Approval extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    public $vendor;
    /**
     * @var Data
     */
    public $helper;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Approval constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param Data $csmarketplaceHelper
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
        $this->vendor = $vendor;
        $this->helper = $csmarketplaceHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_getSession()->isLoggedIn() && $this->helper->authenticate($this->session->getCustomerId())) {
            $resultRedirect->setPath('*/vendor/');
            return $resultRedirect;
        }

        if (!$this->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $this->session->unsVendorId();
        $this->session->unsVendor();
        if ($this->session->isLoggedIn()) {

            $vendor = $this->vendor->create()->loadByCustomerId($this->session->getCustomerId());
            if ($vendor && $vendor->getId()) {
                $this->session->setData('vendor_id', $vendor->getId());
                $this->session->setData('vendor', $vendor->getData());
            }
        }
        $resultPage = $this->resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->set(__('Account Approval'));
        return $resultPage;
    }
}
