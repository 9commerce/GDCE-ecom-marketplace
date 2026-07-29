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

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class PrintAction extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Ced\CsOrder\Model\Order\Pdf\Shipment
     */
    protected $pdfShipment;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment
     */
    protected $_shipmentResource;

    /**
     * PrintAction constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param FileFactory $fileFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsOrder\Model\Order\Pdf\Shipment $pdfShipment
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
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
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Sales\Model\Order\Shipment $shipment,
        \Magento\Sales\Model\ResourceModel\Order\Shipment $shipmentResource,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsOrder\Model\Order\Pdf\Shipment $pdfShipment,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->_fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $registry;
        $this->shipment = $shipment;
        $this->_shipmentResource = $shipmentResource;
        $this->vorders = $vorders;
        $this->pdfShipment = $pdfShipment;
        $this->dateTime = $dateTime;
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
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $vendorId = $this->session->getVendorId();
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->shipment;
            $this->_shipmentResource->load($shipment, $shipmentId);

            $vorder = $this->vorders->setVendorId($vendorId)->getVorderByShipment($shipment);

            $this->_coreRegistry->register('current_vorder', $vorder);

            if ($shipment) {
                $pdf = $this->pdfShipment->getPdf([$shipment]);
                $date = $this->dateTime->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create(
                    'packingslip' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            /**
             * @var \Magento\Backend\Model\View\Result\Forward $resultForward
             */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        return false;
    }
}
