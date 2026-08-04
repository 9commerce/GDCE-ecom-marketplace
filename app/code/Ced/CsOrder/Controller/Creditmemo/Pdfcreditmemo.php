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

use Ced\CsOrder\Model\Order\Pdf\Creditmemo;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\UrlFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdfcreditmemo extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var mixed
     */
    protected $filter;

    /**
     * @var mixed
     */
    protected $creditCollectionFactory;

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
     * @var Creditmemo
     */
    protected $pdfCreditmemo;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Ced\CsOrder\Model\Creditmemo
     */
    protected $creditmemo;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
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
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        \Ced\CsOrder\Model\Order\Pdf\Creditmemo $pdfCreditmemo,
        CreditmemoRepositoryInterface $creditmemoRepository,
        \Ced\CsOrder\Model\Creditmemo $creditmemo
    ) {
        $this->filter = $filter;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfCreditmemo = $pdfCreditmemo;
        $this->creditCollectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemo = $creditmemo;
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
            $collection = $this->filter->getCollection($this->creditCollectionFactory->create());
            $vendorId = $this->customerSession->getVendorId();
            $creditMemoIds = $collection->getColumnValues('entity_id');
            foreach ($creditMemoIds as $creditMemoId) {
                $creditMemo = $this->creditmemoRepository->get($creditMemoId);
                $this->creditmemo->setVendorId($vendorId)->updateTotal($creditMemo);
                $creditMemoData [] = $creditMemo;
            }
            if ($creditMemoData) {
                return $this->massAction($creditMemoData);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     * @throws \Zend_Pdf_Exception
     */
    public function massAction($creditCollection)
    {
        $pdf = $this->pdfCreditmemo->getPdf($creditCollection);
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->fileFactory->create(
            sprintf('creditmemo%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
