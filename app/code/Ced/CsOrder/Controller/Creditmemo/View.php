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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Controller\Creditmemo;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class View extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo
     */
    protected $creditmemoResource;

    /**
     * @var \Ced\CsOrder\Model\Creditmemo
     */
    protected $csorderCreditmemo;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param \Ced\CsOrder\Model\Creditmemo $csorderCreditmemo
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo $creditmemoResource,
        \Ced\CsOrder\Model\Creditmemo $csorderCreditmemo
    ) {
        $this->registry = $registry;
        $this->session = $customerSession;
        $this->creditmemo = $creditmemo;
        $this->creditmemoResource =  $creditmemoResource;
        $this->csorderCreditmemo = $csorderCreditmemo;
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
     * Creditmemo information page
     * @return \Magento\Framework\Message\ManagerInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $coreRegistry = $this->registry;
        $Creditmemo_id = $this->getRequest()->getParam('creditmemo_id');
        $vendorId = $this->session->getVendorId();
        if ($Creditmemo_id) {
            $Creditmemo = $this->creditmemo;
            $this->creditmemoResource->load($Creditmemo, $Creditmemo_id);
            $this->csorderCreditmemo->setVendorId($vendorId)->updateTotal($Creditmemo);
            $coreRegistry->register('current_creditmemo', $Creditmemo, true);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Creditmemo') . ' # ' . $Creditmemo->getIncrementId());
            return $resultPage;
        } else {
            return $this->messageManager->addErrorMessage(__('Creditmemo Does not exists.'));
        }
    }
}
