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
use Ced\CsPurchaseOrder\Model\PurchaseorderFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History;
use Ced\CsPurchaseOrder\Model\HistoryFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ced\CsPurchaseOrder\Model\Quote\Source\Status;
use Ced\CsPurchaseOrder\Model\Quote\Source\Users;
use Ced\CsPurchaseOrder\Model\Quote\Source\LogStatus;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Ced\CsPurchaseOrder\Model\VendorStatusFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus as VendorStatusResource;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AcceptbyVendor
 * @package Ced\CsPurchaseOrder\Controller\Quotations
 */
class AcceptbyVendor extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PurchaseorderFactory
     */
    protected $purchaseOrderFactory;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * @var
     */
    protected $dateTime;

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

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
    protected $serializer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * AcceptbyVendor constructor.
     * @param PurchaseorderFactory $purchaseOrderFactory
     * @param Purchaseorder $purchaseorderResource
     * @param DateTime $dateTime
     * @param VendorStatusFactory $vendorStatusFactory
     * @param VendorStatusResource $vendorStatusResource
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $session
     * @param ProductRepository $productRepository
     * @param HistoryFactory $historyFactory
     * @param History $historyResource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param Json $serializer
     * @param Data $helper
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        PurchaseorderFactory $purchaseOrderFactory,
        Purchaseorder $purchaseorderResource,
        DateTime $dateTime,
        VendorStatusFactory $vendorStatusFactory,
        VendorStatusResource $vendorStatusResource,
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $session,
        ProductRepository $productRepository,
        HistoryFactory $historyFactory,
        History $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        Json $serializer,
        Data $helper,
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->purchaseorderResource = $purchaseorderResource;
        $this->date = $dateTime;
        $this->vendorStatusFactory = $vendorStatusFactory;
        $this->vendorStatusResource = $vendorStatusResource;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        if (!$this->scopeConfig->getValue('ced_purchaseorder/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $this->_redirect('customer/account');
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('requestid') && $this->getRequest()->getParam('status_id')
            && $this->getRequest()->getParam('product_id')) {
            try {
                $quote_id = $this->getRequest()->getParam('requestid');
                $product_id = $this->getRequest()->getParam('product_id');
                $vendor_id = $this->session->getVendorId();
                $product_name = $this->productRepository->getById($product_id)->getName();

                /*updating vendor status*/

                $negotiatedqty = '';
                $negotiatedprice = '';
                $vendors = $this->vendorStatusCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id',$quote_id)
                    ->addFieldToFilter('vendor_status', ['nin' => [VendorStatus::REJECTED_BY_VENDOR,
                        VendorStatus::REJECTED_BY_CUSTOMER]]);
                $vendorStatus = $this->vendorStatusFactory->create();
                foreach ($vendors as $vendor) {

                    $this->vendorStatusResource->load($vendorStatus, $vendor->getId());
                    if ($vendor->getVendorId() == $vendor_id) {
                        $vendorStatus->setIsApproved(1)
                            ->setVendorStatus(VendorStatus::APPROVED_BY_VENDOR);
                        $negotiatedprice = $vendorStatus->getNegotiationPrice();
                        $negotiatedqty = $vendorStatus->getNegotiationQty();
                    } else {
                        $vendorStatus->setVendorStatus(VendorStatus::REJECTED_BY_CUSTOMER);
                    }
                    $this->vendorStatusResource->save($vendorStatus);
                }

                /*Updating Quote Status*/

                $model = $this->purchaseOrderFactory->create();
                $this->purchaseorderResource->load($model, $quote_id);
                $model->setStatus(Status::APPROVED)
                    ->setUpdatedAt($this->date->timestamp())
                    ->setNegotiatedFinalPrice($negotiatedprice)
                    ->setNegotiatedFinalQty($negotiatedqty)
                    ->setProductId($product_id)
                    ->setProductName($product_name);

                /*saving log in history*/

                $previousHistory = $this->historyCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $quote_id)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->getLastItem();

                $previouslogData = $this->serializer->unserialize($previousHistory->getLogData());

                $logData = [

                    'comments' => null,
                    'status' => [
                        'quote_status' =>
                            [
                                'old_value' => $previouslogData['status']['quote_status']['new_value'],
                                'new_value' => Status::APPROVED
                            ],
                        'quote_vendor_status' =>
                            [
                                'old_value' => $previouslogData['status']['quote_vendor_status']['new_value'],
                                'new_value' => VendorStatus::APPROVED_BY_VENDOR
                            ]
                    ],
                    'product_info' => [
                        'product_id' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_id']['new_value'],
                                'new_value' => $product_id
                            ],
                        'product_name' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_name']['new_value'],
                                'new_value' => $product_name
                            ],
                        'product_qty' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $previouslogData['product_info']['product_qty']['new_value']
                            ],
                        'product_price' =>
                            [
                                'old_value' => $previouslogData['product_info']['product_qty']['new_value'],
                                'new_value' => $previouslogData['product_info']['product_qty']['new_value']
                            ]
                    ]
                ];

                $history = $this->historyFactory->create();
                $history->setCQuoteId($quote_id)
                    ->setVendorId($vendor_id)
                    ->setAuthorId($this->session->getCustomerId())
                    ->setWhoIs(Users::CUSTOMER)
                    ->setStatus(LogStatus::UPDATED)
                    ->setLogData($this->serializer->serialize($logData));
                $this->historyResource->save($history);
                $this->purchaseorderResource->save($model);

                $this->helper->sendApprovedEmailToCustomer($quote_id);
                $this->helper->sendApprovedEmailToVendors($quote_id, $vendor_id);

                $this->messageManager->addSuccessMessage(__('You Accepted The Quotation'));
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
        return $resultRedirect->setPath('cspurchaseorder/quotations/qlist');
    }
}