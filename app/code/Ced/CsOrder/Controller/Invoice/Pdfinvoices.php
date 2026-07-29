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
namespace Ced\CsOrder\Controller\Invoice;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\UrlFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsOrder\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
//use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdfinvoices extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Invoice
     */
    protected $pdfInvoice;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context            $context,
        \Magento\Framework\View\Result\PageFactory       $resultPageFactory,
        Session                                          $customerSession,
        UrlFactory                                       $urlFactory,
        \Magento\Framework\Registry                      $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data                   $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl                    $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory           $vendor,
        Filter                                           $filter,
        DateTime                                         $dateTime,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        CollectionFactory $orderCollection,
        ManagerInterface $messageManager,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Ced\CsOrder\Model\InvoiceFactory $invoiceFactory
    ) {
        $this->filter = $filter;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->ordersCollection = $orderCollection;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceFactory = $invoiceFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $vCollection = $this->filter->getCollection($this->ordersCollection->create());
            $invoiceIds = $vCollection->getColumnValues('entity_id');
            $vendorId = $this->customerSession->getVendorId();
            foreach ($invoiceIds as $invoiceId) {
                $invoice= $this->invoiceRepository->get($invoiceId);
                $this->invoiceFactory->create()->setVendorId($vendorId)->updateTotal($invoice, true);
                $invoiceData [] = $invoice;
            }
            return $this->massAction($invoiceData);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function massAction($invoice)
    {
        $pdf = $this->pdfInvoice->getPdf($invoice);
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->fileFactory->create(
            sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
