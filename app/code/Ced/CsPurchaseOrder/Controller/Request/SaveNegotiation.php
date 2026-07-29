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

namespace Ced\CsPurchaseOrder\Controller\Request;

use Ced\CsPurchaseOrder\Helper\Data;
use Ced\CsPurchaseOrder\Model\CommentsFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Comments;
use Ced\CsPurchaseOrder\Model\VendorStatusFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus as VendorStatusResource;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Ced\CsPurchaseOrder\Model\Quote\Source\Status;
use Ced\CsPurchaseOrder\Model\Quote\Source\Users;
use Ced\CsPurchaseOrder\Model\Quote\Source\LogStatus;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Magento\Catalog\Model\ProductRepository;
use Ced\CsPurchaseOrder\Model\HistoryFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History;
use Ced\CsPurchaseOrder\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SaveNegotiation
 * @package Ced\CsPurchaseOrder\Controller\Request
 */
class SaveNegotiation extends \Magento\Framework\App\Action\Action
{

    /**
     * @var CommentsFactory
     */
    protected $commentsFactory;

    /**
     * @var Comments
     */
    protected $commentsResource;

    /**
     * @var VendorStatusFactory
     */
    protected $vendorStatusFactory;

    /**
     * @var VendorStatusResource
     */
    protected $vendorStatusResource;

    /**
     * @var VendorStatusCollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var History
     */
    protected $historyResource;

    /**
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var Json
     */
    protected $seriaizer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * SaveNegotiation constructor.
     * @param CommentsFactory $commentsFactory
     * @param Comments $commentsResource
     * @param VendorStatusFactory $vendorStatusFactory
     * @param VendorStatusResource $vendorStatusResource
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param SessionFactory $sessionFactory
     * @param ProductRepository $productRepository
     * @param HistoryFactory $historyFactory
     * @param History $historyResource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param Json $seriaizer
     * @param Data $helper
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        CommentsFactory $commentsFactory,
        Comments $commentsResource,
        VendorStatusFactory $vendorStatusFactory,
        VendorStatusResource $vendorStatusResource,
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        SessionFactory $sessionFactory,
        ProductRepository $productRepository,
        HistoryFactory $historyFactory,
        History $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        Json $seriaizer,
        Data $helper,
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->commentsFactory = $commentsFactory;
        $this->commentsResource = $commentsResource;
        $this->vendorStatusFactory = $vendorStatusFactory;
        $this->vendorStatusResource = $vendorStatusResource;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->sessionFactory = $sessionFactory;
        $this->productRepository = $productRepository;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->helper = $helper;
        $this->seriaizer = $seriaizer;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $postdata = $this->getRequest()->getPostValue();

        if ($postdata) {

            try {
                $customer_id = $this->sessionFactory->create()->getCustomerId();
                $product_name = $this->productRepository->getById($postdata['product_id'])->getName();

                /*Setting comments*/
                $comments = $this->commentsFactory->create();
                $comments->setCQuoteId($postdata['quote_id'])
                    ->setAuthorId($customer_id)
                    ->setNegotiationQty($postdata['n_qty'])
                    ->setNegotiationPrice($postdata['n_price'])
                    ->setComments($postdata['comments'])
                    ->setWhoIs(Users::CUSTOMER)
                    ->setVendorId($postdata['vendor_id'])
                    ->setProductId($postdata['product_id']);
                $this->commentsResource->save($comments);

                /*Updating the status*/
                $status = $this->vendorStatusCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $postdata['quote_id'])
                    ->addFieldToFilter('vendor_id', $postdata['vendor_id'])
                    ->getLastItem();

                if ($status->getId()) {
                    $vendorStatus = $this->vendorStatusFactory->create();
                    $this->vendorStatusResource->load($vendorStatus, $status->getId());
                    $vendorStatus->setVendorStatus(VendorStatus::UPDATED_BY_CUSTOMER)
                        ->setWhoIs(Users::CUSTOMER)
                        ->setAuthorId($customer_id)
                        ->setNegotiationPrice($postdata['n_price'])
                        ->setNegotiationQty($postdata['n_qty'])
                        ->setProductName($product_name);
                    $this->vendorStatusResource->save($vendorStatus);
                }

                /*saving log in history*/
                $previousHistory = $this->historyCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $postdata['quote_id'])
                    ->addFieldToFilter('vendor_id', $postdata['vendor_id'])
                    ->getLastItem();

                //print_r($previousHistory->getLogData()); die(__FILE__);
                $previouslogData = $this->seriaizer->unserialize($previousHistory->getLogData());

                $logData = [

                    'comments' => $comments->getId(),
                    'status' => [
                        'quote_status' =>
                            [
                                'old_value' => $previouslogData['status']['quote_status']['new_value'],
                                'new_value' => Status::PROCESSING
                            ],
                        'quote_vendor_status' =>
                            [
                                'old_value' => $previouslogData['status']['quote_vendor_status']['new_value'],
                                'new_value' => VendorStatus::UPDATED_BY_CUSTOMER
                            ]
                    ],
                    'product_info' => [
                        'product_id' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_id']['new_value'],
                                'new_value' => $postdata['product_id']
                            ],
                        'product_name' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_name']['new_value'],
                                'new_value' => $product_name
                            ],
                        'product_qty' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $postdata['n_qty']
                            ],
                        'product_price' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $postdata['n_price']
                            ]
                    ]
                ];

                $history = $this->historyFactory->create();
                $history->setCQuoteId($postdata['quote_id'])
                    ->setVendorId($postdata['vendor_id'])
                    ->setAuthorId($postdata['vendor_id'])
                    ->setWhoIs(Users::CUSTOMER)
                    ->setStatus(LogStatus::UPDATED)
                    ->setLogData($this->seriaizer->serialize($logData));
                $this->historyResource->save($history);

                $this->helper->sendUpdateEmailToCustomer($postdata['quote_id']);
                $this->helper->sendUpdateEmailToVendors($postdata['quote_id'], $postdata['vendor_id']);

                $this->messageManager->addSuccessMessage(__('Your Quote Has Been Sent Successfully'));
                return $this->_redirect('cspurchaseorder/request/view');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the request.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something Went Wrong While Saving Request'));
            return $this->_redirect('cspurchaseorder/request/view');
        }
        return $this->_redirect('cspurchaseorder/request/view');
    }
}
