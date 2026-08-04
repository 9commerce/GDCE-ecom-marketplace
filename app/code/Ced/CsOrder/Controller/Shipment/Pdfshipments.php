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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\UrlFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsOrder\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

class Pdfshipments extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var mixed
     */
    protected $filter;

    /**
     * @var mixed
     */
    protected $collectionFactory;

    /**
     * @var mixed
     */
    protected $customerSession;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Shipment
     */
    protected $pdfShipment;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Shipment $shipment
     * @param CollectionFactory $collectionFactory
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
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Filter $filter,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Shipment $shipment,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager
    )
    {
        $this->filter = $filter;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->pdfShipment = $shipment;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }
    /**
     * @param AbstractCollection $collection
     * @return $this|ResponseInterface
     * @throws \Exception
     */
    public function massAction($collection)
    {
        $pdf = $this->pdfShipment->getPdf($collection);
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->fileFactory->create(
            sprintf('packingslip%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
