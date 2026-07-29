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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Controller\Vpayments;

use Ced\CsMarketplace\Model\Vpayment\Requested;
use Magento\Framework\App\Action\Context;

class Requestpost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_getSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Ced\CsTransaction\Model\ItemsFactory
     */
    protected $_vtItemsFactory;

    /**
     * @var \Ced\CsTransaction\Model\ResourceModel\Items
     */
    protected $_vtItemsResource;

    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\RequestedFactory
     */
    protected $_requestedFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Requested
     */
    protected $requestedResource;

    /**
     * Requestpost constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory
     * @param \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource
     * @param \Ced\CsMarketplace\Model\Vpayment\RequestedFactory $requestedFactory
     * @param \Ced\CsMarketplace\Model\ResourceModel\Requested $requestedResource
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Ced\CsTransaction\Model\ItemsFactory $vtItemsFactory,
        \Ced\CsTransaction\Model\ResourceModel\Items $vtItemsResource,
        \Ced\CsMarketplace\Model\Vpayment\RequestedFactory $requestedFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Requested $requestedResource
    ) {
        $this->_getSession = $customerSession;
        $this->datetime = $datetime;
        $this->_vtItemsFactory = $vtItemsFactory;
        $this->_vtItemsResource = $vtItemsResource;
        $this->_requestedFactory = $requestedFactory;
        $this->requestedResource = $requestedResource;
        parent::__construct($context);
    }

    /**
     * @return $this|bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_getSession->getVendorId()) {
            return false;
        }
        $orderIds = $this->getRequest()->getParam('payment_request');
        if (strlen($orderIds) > 0) {
            $orderIds = explode(',', $orderIds);
        }

        if (!is_array($orderIds)) {
            $this->messageManager->addErrorMessage(__('Please select amount(s).'));
        } else {
            if (!empty($orderIds)) {
                try {
                    $updated = 0;
                    foreach ($orderIds as $orderId) {
                        $items_model = $this->_vtItemsFactory->create();
                        $this->_vtItemsResource->load($items_model, $orderId);
                        $amount = $items_model->getItemFee();
                        $order_increment_id = $items_model->getOrderIncrementId();

                        $data = [
                            'vendor_id' => $this->_getSession->getVendorId(),
                            'order_id' => $order_increment_id,
                            'amount' => $amount,
                            'status' => Requested::PAYMENT_STATUS_REQUESTED,
                            'created_at' => $this->datetime->date('Y-m-d H:i:s'),
                            'vorder_item_id' => $items_model->getId()
                        ];
                        $items_model->setIsRequested(Requested::PAYMENT_STATUS_REQUESTED)->save();
                        $requestedModel = $this->_requestedFactory->create()->addData($data);
                        $this->requestedResource->save($requestedModel);
                        $updated++;
                    }
                    if ($updated) {
                        $this->messageManager->addSuccessMessage(
                            __('Total of %1 amount(s) have been requested for payment.', $updated)
                        );
                    } else {
                        $this->messageManager->addSuccessMessage(
                            __('Payment(s) have been already requested for payment.')
                        );
                    }
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                }
            }
        }
        return false;
    }
}
