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
namespace Ced\CsMarketplace\Controller\Vpayments;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\UrlFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsMarketplace\Model\Order\Pdf\Transaction;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Ced\CsMarketplace\Model\ResourceModel\Vpayment\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdftransaction extends \Ced\CsMarketplace\Controller\Vendor
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
     * @var Transaction
     */
    protected $pdfTransaction;

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
        Transaction $pdfTransaction,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\Redirect $resultFactory
    ) {
        $this->filter = $filter;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfTransaction = $pdfTransaction;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
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
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('csmarketplace/vpayments/index/');
        }
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function massAction($collection)
    {
        $pdf = $this->pdfTransaction->getPdf($collection);
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->fileFactory->create(
            sprintf('transaction%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
