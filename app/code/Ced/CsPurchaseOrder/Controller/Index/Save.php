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

namespace Ced\CsPurchaseOrder\Controller\Index;

use Magento\Framework\App\Action\Context;
use Ced\CsPurchaseOrder\Helper\Data;
use Ced\CsPurchaseOrder\Model\PurchaseorderFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;
use Ced\CsPurchaseOrder\Model\AttachmentsFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Attachments;
use Ced\CsPurchaseOrder\Model\HistoryFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\History;
use Ced\CsPurchaseOrder\Model\Quote\Source\Status;
use Ced\CsPurchaseOrder\Model\Quote\Source\Users;
use Ced\CsPurchaseOrder\Model\Quote\Source\LogStatus;
use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Magento\Customer\Model\CustomerFactory;
use Ced\CsPurchaseOrder\Model\VendorStatusFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus as VendorStatusResource;

/**
 * Class Save
 * @package Ced\CsPurchaseOrder\Controller\Index
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * @var AttachmentsFactory
     */
    protected $attachmentsFactory;

    /**
     * @var Attachments
     */
    protected $attachmentsResource;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var History
     */
    protected $historyResourceModel;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var VendorStatusFactory
     */
    protected $vendorStatusFactory;

    /**
     * @var VendorStatusResource
     */
    protected $vendorStatusResource;

    /**
     * Save constructor.
     * @param Context $context
     * @param Data $helper
     * @param PurchaseorderFactory $purchaseorderFactory
     * @param Purchaseorder $purchaseorderResource
     * @param AttachmentsFactory $attachmentsFactory
     * @param Attachments $attachmentsResource
     * @param HistoryFactory $historyFactory
     * @param History $historyResourceModel
     * @param CustomerFactory $customerFactory
     * @param VendorStatusFactory $vendorStatusFactory
     * @param VendorStatusResource $vendorStatusResource
     */
    public function __construct(
        Context $context,
        Data $helper,
        PurchaseorderFactory $purchaseorderFactory,
        Purchaseorder $purchaseorderResource,
        AttachmentsFactory $attachmentsFactory,
        Attachments $attachmentsResource,
        HistoryFactory $historyFactory,
        History $historyResourceModel,
        CustomerFactory $customerFactory,
        VendorStatusFactory $vendorStatusFactory,
        VendorStatusResource $vendorStatusResource
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->purchaseorderResource = $purchaseorderResource;
        $this->attachmentsFactory = $attachmentsFactory;
        $this->attachmentsResource = $attachmentsResource;
        $this->historyFactory = $historyFactory;
        $this->historyResourceModel = $historyResourceModel;
        $this->customerFactory = $customerFactory;
        $this->vendorStatusFactory = $vendorStatusFactory;
        $this->vendorStatusResource = $vendorStatusResource;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $postdata = $this->getRequest()->getPostValue();
        //echo $_SERVER['REMOTE_ADDR'];die('----');
        //echo $this->getRequest()->getServer('REMOTE_ADDR');die('----');
        if ($postdata) {

            try {

                /*saving quote data*/

                $postdata['status'] = 'new';
                $postdata['description'] = $postdata['comments'];
                $postdata['remote_ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
                $postdata['terms_and_conditions'] = 1;
                $postdata['customer_email'] = $this->customerFactory->create()
                    ->load($postdata['customer_id'])->getEmail();

                $model = $this->purchaseorderFactory->create();
                $model->setData($postdata);
                $this->purchaseorderResource->save($model);

                /*saving data in attachments*/

                $filedata = $this->helper->uploadDocument('customer', null, null);
                $filedata['c_quote_id'] = $model->getId();

                $attachments = $this->attachmentsFactory->create()->setData($filedata);
                $this->attachmentsResource->save($attachments);

                /*Saving data in history*/

                $logData = [
                    'status' => [
                        'quote_status' =>
                            [
                                'new_value' => Status::NEW
                            ],
                        'quote_vendor_status' =>
                            [
                                'new_value' => VendorStatus::NEW
                            ]
                    ]
                ];

                $assignedVendors = $this->helper->getAssignedVendors($postdata['category_id']);
                foreach ($assignedVendors as $vendor) {
                    $historyData = [
                        'c_quote_id' => $model->getId(),
                        'vendor_id' => $vendor,
                        'author_id' => $vendor,
                        'who_is' => Users::VENDOR,
                        'status' => LogStatus::CREATED,
                        'log_data' => json_encode($logData)
                    ];

                    $history = $this->historyFactory->create();
                    $history->setData($historyData);
                    $this->historyResourceModel->save($history);

                    /*Saving data in vendor status*/

                    $statusData = [
                        'c_quote_id' => $model->getId(),
                        'vendor_id' => $vendor,
                        'vendor_status' => VendorStatus::NEW,
                        'who_is' => Users::CUSTOMER,
                        'author_id' => $vendor,
                        'is_approved' => 0
                    ];

                    $vendorStatus = $this->vendorStatusFactory->create()->setData($statusData);
                    $this->vendorStatusResource->save($vendorStatus);
                }

                $this->helper->sendEmailToCustomer($model->getId());
                $this->helper->sendEmailToVendors($model->getId());
                $this->messageManager->addSuccessMessage(__('Your Request Has Been Submited Successfully'));
                return $this->_redirect('cspurchaseorder/request/view');
            } catch
            (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the request.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something Went Wrong While Saving Request'));
            return $this->_redirect('cspurchaseorder/request/view');
        }
    }
}
