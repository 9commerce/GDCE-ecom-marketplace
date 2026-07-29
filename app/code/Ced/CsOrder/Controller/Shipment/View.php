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

namespace Ced\CsOrder\Controller\Shipment;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class View extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsOrder\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment
     */
    protected $_shipmentResource;

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
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource
     * @param \Ced\CsOrder\Model\Vorders $vorders
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
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource,
        \Ced\CsOrder\Model\Vorders $vorders
    ) {
        $this->registry = $registry;
        $this->shipment = $shipment;
        $this->_shipmentResource = $shipmentResource;
        $this->vorders = $vorders;
        $this->session = $customerSession;
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
     * @return \Magento\Framework\Message\ManagerInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $vendorId = $this->session->getVendorId();
        $coreRegistry = $this->registry;
        $Shipment_id = $this->getRequest()->getParam('shipment_id');
        if ($Shipment_id) {
            $shipment = $this->shipment;
            $this->_shipmentResource->load($shipment, $Shipment_id);
            $coreRegistry->register('current_shipment', $shipment, true);
            $vorder = $this->vorders->setVendorId($vendorId)->getVorderByShipment($shipment);
            $coreRegistry->register('current_vorder', $vorder, true);

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Shipment') . ' # ' . $shipment->getIncrementId());
            return $resultPage;
        } else {
            return $this->messageManager->addErrorMessage(__('Shipment Does not exists.'));
        }
    }
}
