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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsTransaction\Controller\Vpayments;

use Ced\CsMarketplace\Controller\Vendor;
use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\Vpayment\Requested;
use Ced\CsMarketplace\Model\Vpayment\RequestedFactory;
use Ced\CsTransaction\Model\ResourceModel\Items;
use Ced\CsTransaction\Model\ResourceModel\Items\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassRequest extends Vendor
{
    /**
     * @var Session
     */
    public Session $_getSession;
    /**
     * @var CollectionFactory
     */
    public CollectionFactory $collectionFactory;
    /**
     * @var Filter
     */
    public Filter $filter;
    /**
     * @var RequestedFactory
     */
    public RequestedFactory $_requestedFactory;
    /**
     * @var Items
     */
    public Items $itemsResource;
    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Requested
     */
    public \Ced\CsMarketplace\Model\ResourceModel\Requested $requestedResource;
    /**
     * @var DateTime
     */
    public DateTime $datetime;

    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Items $itemsResource,
        RequestedFactory $requestedFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Requested $requestedResource,
        DateTime $datetime
    ) {
        $this->_getSession = $customerSession;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->itemsResource = $itemsResource;
        $this->_requestedFactory = $requestedFactory;
        $this->requestedResource = $requestedResource;
        $this->datetime = $datetime;
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
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_getSession->getVendorId()) {
            return false;
        }
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('cstransaction/vpayments/request');
        }
        if ($collection->getSize() > 0) {
            try {
                $updated = 0;
                foreach ($collection as $model) {
                    if ($model->getIsRequested() == 1 && $model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY) {
                        $this->messageManager->addNoticeMessage(
                            "Few items of Order with ID " . $model->getOrderIncrementId() . " are already requested"
                        );
                    } elseif ($model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_PAID) {
                        $this->messageManager->addNoticeMessage(
                            "Few items of Order with ID " . $model->getOrderIncrementId() . " are already paid"
                        );
                    } elseif ($model->getQtyOrdered() == $model->getQtyRefunded()) {
                        $this->messageManager->addNoticeMessage(
                            "Few items of Order with ID " . $model->getOrderIncrementId() . " are already cancelled"
                        );
                    } elseif ($model->getQtyOrdered() == $model->getQtyReadyToPay() + $model->getQtyRefunded()) {
                        $amount = $model->getItemFee();
                        $order_increment_id = $model->getOrderIncrementId();

                        $data = [
                            'vendor_id' => $this->_getSession->getVendorId(),
                            'order_id' => $order_increment_id,
                            'amount' => $amount,
                            'status' => Requested::PAYMENT_STATUS_REQUESTED,
                            'created_at' => $this->datetime->date('Y-m-d H:i:s'),
                            'vorder_item_id' => $model->getId()
                        ];
                        $model->setIsRequested(Requested::PAYMENT_STATUS_REQUESTED);
                        $this->itemsResource->save($model);
                        $requestedModel = $this->_requestedFactory->create()->addData($data);
                        $this->requestedResource->save($requestedModel);
                        $updated++;
                    } else {
                        $this->messageManager->addErrorMessage(
                            "Few items of Order with ID " . $model->getOrderIncrementId() . " are not allowed"
                        );
                    }
                }
                if ($updated) {
                    $this->messageManager->addSuccessMessage(
                        __('Total of %1 amount(s) have been requested for payment.', $updated)
                    );
                }
                return $resultRedirect->setPath('cstransaction/vpayments/request');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('cstransaction/vpayments/request');
            }
        }
        return false;
    }
}
