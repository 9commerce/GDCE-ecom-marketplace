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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Controller\Quotations;

use Ced\CsPurchaseOrder\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Ced\CsPurchaseOrder\Model\VendorStatusFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus as VendorStatusResource;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Ced\CsPurchaseOrder\Model\HistoryFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History;
use Ced\CsPurchaseOrder\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Ced\CsPurchaseOrder\Model\CommentsFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Comments;
use Ced\CsPurchaseOrder\Model\Quote\Source\Status;
use Ced\CsPurchaseOrder\Model\Quote\Source\Users;
use Ced\CsPurchaseOrder\Model\Quote\Source\LogStatus;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Magento\Catalog\Model\ProductRepository;
use Ced\CsPurchaseOrder\Model\PurchaseorderFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;

/**
 * Class Savequotation
 * @package Ced\CsPurchaseOrder\Controller\Quotations
 */
class Savequotation extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var VendorStatusFactory
     */
    protected $vendorStatusFactory;

    /**
     * @var CommentsFactory
     */
    protected $purchaseOrderComments;

    /**
     * @var Comments
     */
    protected $commentsResource;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var VendorStatusCollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var VendorStatusResource
     */
    protected $vendorStatusResource;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var History
     */
    protected $historyResource;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Savequotation constructor.
     * @param VendorStatusFactory $vendorStatusFactory
     * @param VendorStatusResource $vendorStatusResource
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param CommentsFactory $purchaseOrderComments
     * @param Comments $commentsResource
     * @param HistoryFactory $historyFactory
     * @param History $historyResource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param ProductRepository $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param PurchaseorderFactory $purchaseorderFactory
     * @param Purchaseorder $purchaseorderResource
     * @param Data $helper
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
        VendorStatusFactory $vendorStatusFactory,
        VendorStatusResource $vendorStatusResource,
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        CommentsFactory $purchaseOrderComments,
        Comments $commentsResource,
        HistoryFactory $historyFactory,
        History $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        ProductRepository $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        PurchaseorderFactory $purchaseorderFactory,
        Purchaseorder $purchaseorderResource,
        Data $helper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
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

        $this->vendorStatusFactory = $vendorStatusFactory;
        $this->purchaseOrderComments = $purchaseOrderComments;
        $this->commentsResource = $commentsResource;
        $this->session = $customerSession;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->vendorStatusResource = $vendorStatusResource;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->productRepository = $productRepository;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->serializer = $serializer;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->purchaseorderResource = $purchaseorderResource;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data && $this->getRequest()->getParam('id')) {

            try {

                $quote_id = $this->getRequest()->getParam('id');
                $vendor_id = $this->session->getVendorId();
                $product_name = $this->productRepository->getById($data['product_id'])->getName();

                /* Saving data in comments*/

                $model = $this->purchaseOrderComments->create();
                $model->setCQuoteId($quote_id)
                    ->setAuthorId($vendor_id)
                    ->setComments($data['comments'])
                    ->setNegotiationPrice($data['nprice'])
                    ->setNegotiationQty($data['nqty'])
                    ->setWhoIs(1)
                    ->setVendorId($vendor_id)
                    ->setProductId($data['product_id']);
                $this->commentsResource->save($model);

                /*Updating the status*/

                $status = $this->vendorStatusCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $quote_id)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->getLastItem();

                if ($status->getId()) {
                    $vendorStatus = $this->vendorStatusFactory->create();
                    $this->vendorStatusResource->load($vendorStatus, $status->getId());
                    $vendorStatus->setVendorStatus(VendorStatus::UPDATED_BY_VENDOR)
                        ->setWhoIs(Users::VENDOR)
                        ->setAuthorId($vendor_id)
                        ->setNegotiationPrice($data['nprice'])
                        ->setNegotiationQty($data['nqty'])
                        ->setProductId($data['product_id'])
                        ->setProductName($product_name)
                        ->setVendorReplied(1);
                    $this->vendorStatusResource->save($vendorStatus);
                }

                /*Saving data in history*/

                $previousHistory = $this->historyCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $quote_id)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->getLastItem();

                $previouslogData = $this->serializer->unserialize($previousHistory->getLogData());

                $logData = [

                    'comments' => $model->getId(),
                    'status' => [
                        'quote_status' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    $previouslogData['status']['quote_status']['new_value'] :
                                    $previouslogData['status']['quote_status']['new_value'],
                                'new_value' => Status::PROCESSING
                            ],
                        'quote_vendor_status' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    $previouslogData['status']['quote_status']['new_value'] :
                                    $previouslogData['status']['quote_vendor_status']['new_value'],
                                'new_value' => VendorStatus::UPDATED_BY_VENDOR
                            ]
                    ],
                    'product_info' => [
                        'product_id' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    null : $previouslogData['product_info']['product_id']['new_value'],
                                'new_value' => $data['product_id']
                            ],
                        'product_name' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    null : $previouslogData['product_info']['product_name']['new_value'],
                                'new_value' => $product_name
                            ],
                        'product_qty' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    null : $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $data['nqty']
                            ],
                        'product_price' =>
                            [
                                'old_value' => $previousHistory->getStatus() == LogStatus::CREATED ?
                                    null : $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $data['nprice']
                            ]
                    ]
                ];

                $historyData = $this->historyFactory->create();
                $historyData->setCQuoteId($quote_id)
                    ->setVendorId($vendor_id)
                    ->setAuthorId($vendor_id)
                    ->setWhoIs(Users::VENDOR)
                    ->setStatus(LogStatus::UPDATED)
                    ->setLogData($this->serializer->serialize($logData));
                $this->historyResource->save($historyData);

                /* Updating Quote Status*/

                $purchaseOrder = $this->purchaseorderFactory->create();
                $this->purchaseorderResource->load($purchaseOrder, $quote_id);
                $purchaseOrder->setStatus(Status::PROCESSING);
                $this->purchaseorderResource->save($purchaseOrder);

                $this->helper->sendUpdateEmailToCustomer($quote_id);
                $this->helper->sendUpdateEmailToVendors($quote_id, $vendor_id);

                $this->messageManager->addSuccessMessage(('Request has been sent successfully'));
                return $resultRedirect->setPath('*/*/qlist');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the request.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No Data To Save'));
        }
        return $resultRedirect->setPath('*/*/qlist');
    }
}
