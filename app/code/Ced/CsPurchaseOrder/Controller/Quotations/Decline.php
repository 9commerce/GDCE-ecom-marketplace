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

use Ced\CsPurchaseOrder\Model\Quote\Source\LogStatus;
use Ced\CsPurchaseOrder\Model\Quote\Source\Status;
use Ced\CsPurchaseOrder\Model\Quote\Source\Users;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History;
use Ced\CsPurchaseOrder\Model\HistoryFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Ced\CsPurchaseOrder\Model\VendorStatusFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus as VendorStatusResource;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Decline
 * @package Ced\CsPurchaseOrder\Controller\Quotations
 */
class Decline extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var \Ced\CsPurchaseOrder\Model\VendorStatusFactory
     */
    protected $vendorStatusFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory
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
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * Decline constructor.
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param VendorStatusFactory $vendorStatusFactory
     * @param VendorStatusResource $vendorStatusResource
     * @param HistoryFactory $historyFactory
     * @param History $historyResource
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param Json $serializer
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
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        VendorStatusFactory $vendorStatusFactory,
        VendorStatusResource $vendorStatusResource,
        HistoryFactory $historyFactory,
        History $historyResource,
        HistoryCollectionFactory $historyCollectionFactory,
        Json $serializer,
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

        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->session = $customerSession;
        $this->vendorStatusFactory = $vendorStatusFactory;
        $this->vendorStatusResource = $vendorStatusResource;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->historyResource = $historyResource;
        $this->historyFactory = $historyFactory;
        $this->serializer = $serializer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->getParam('id')) {

            try {

                $quote_id = $this->getRequest()->getParam('id');
                $vendor_id = $this->session->getVendorId();

                /*updating vendor status*/

                $status = $this->vendorStatusCollectionFactory->create()
                    ->addFieldToFilter('c_quote_id', $quote_id)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->getLastItem();

                $vendorStatus = $this->vendorStatusFactory->create();
                $this->vendorStatusResource->load($vendorStatus, $status->getId());
                $vendorStatus->setVendorStatus(VendorStatus::REJECTED_BY_VENDOR);
                $this->vendorStatusResource->save($vendorStatus);

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
                                'new_value' => Status::PROCESSING
                            ],
                        'quote_vendor_status' =>
                            [
                                'old_value' => $previouslogData['status']['quote_vendor_status']['new_value'],
                                'new_value' => VendorStatus::REJECTED_BY_VENDOR
                            ]
                    ]
                ];

                $history = $this->historyFactory->create();
                $history->setCQuoteId($quote_id)
                    ->setVendorId($vendor_id)
                    ->setAuthorId($vendor_id)
                    ->setWhoIs(Users::VENDOR)
                    ->setStatus(LogStatus::UPDATED)
                    ->setLogData($this->serializer->serialize($logData));
                $this->historyResource->save($history);

                $this->messageManager->addSuccessMessage(('Decline Request has been sent successfully'));
                return $resultRedirect->setPath('*/*/viewassigned');
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
        return $resultRedirect->setPath('*/*/viewassigned');
    }
}
