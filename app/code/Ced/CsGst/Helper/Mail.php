<?php

namespace Ced\CsGst\Helper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
class Mail extends \Ced\CsMarketplace\Helper\Mail
{
    /**
     * \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    public function __construct(
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproductsCollection,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Backend\Model\UrlInterface $urlInterface,
        \Magento\Store\Model\App\Emulation $_appEmulation
       ){
        parent::__construct($addressRenderer,$paymentHelper,$context,$storeManager,$customerFactory,$vendorFactory,$vordersFactory,$registry,$vproductsFactory,$productFactory,$vproductsCollection,$transportBuilder,$urlInterface);
        $this->_appEmulation= $_appEmulation;
       }
    public function sendOrderEmail(\Magento\Sales\Model\Order $order,$type, $vendorId, $vorder)
    {
        $types = [
            \Ced\CsMarketplace\Model\Vorders::ORDER_NEW_STATUS   => self::XML_PATH_ORDER_NEW_EMAIL_TEMPLATE,
            \Ced\CsMarketplace\Model\Vorders::ORDER_CANCEL_STATUS => self::XML_PATH_ORDER_CANCEL_EMAIL_TEMPLATE,
        ];

        if (!isset($types[$type])) {
            return;
        }

        $storeId = $order->getStore()->getId();
        if($type == \Ced\CsMarketplace\Model\Vorders::ORDER_NEW_STATUS) {
            if (!$this->canSendNewOrderEmail($storeId)) {
                return;
            }
        }

        if($type == \Ced\CsMarketplace\Model\Vorders::ORDER_CANCEL_STATUS) {
            if (!$this->canSendCancelOrderEmail($storeId)) {
                return;
            }
        }

        $vendorIds = array();
        foreach($order->getAllItems() as $item){
            if(!in_array($item->getVendorId(), $vendorIds)) { $vendorIds[] = $item->getVendorId();
            }
        }

        if($type == \Ced\CsMarketplace\Model\Vorders::ORDER_CANCEL_STATUS) {
            // Start store emulation process
            $storeId = $this->storeManager->getStore(null)->getId();
            $initialEnvironmentInfo = $this->_appEmulation->startEnvironmentEmulation($storeId);
        }

        foreach($vendorIds as $vendorId){
            $vendor = $this->vendorFactory->create()->load($vendorId);
            if(!$vendor->getId()) {
                continue;
            }

            $vorder = $this->vordersFactory->create()->loadByField(array('order_id','vendor_id'), array($order->getIncrementId(),$vendorId));
            if($this->registry->registry('current_order')!='') {
                $this->registry->unregister('current_order');
            }

            if($this->registry->registry('current_vorder')!='') {
                $this->registry->unregister('current_vorder');
            }
            $this->registry->register('current_order', $order);
            $this->registry->register('current_vorder', $vorder);

            $this->_sendEmailTemplate(
                $types[$type], self::XML_PATH_ORDER_EMAIL_IDENTITY,
                array(
                    'vendor' => $vendor,
                    'order' => $order,
                    'billing' => $order->getBillingAddress(),
                    'payment_html'=>$this->getPaymentHtml($order),
                    'formattedShippingAddress'=>$this->getFormattedShippingAddress($order),
                    'formattedBillingAddress'=>$this->getFormattedBillingAddress($order)
                ),
                $storeId
            );
        }

        if($type == \Ced\CsMarketplace\Model\Vorders::ORDER_CANCEL_STATUS) {
            $this->_appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }

    }

    /**
     * Send corresponding email template
     *
     * @param  string   $emailTemplate  configuration path of email template
     * @param  string   $emailSender    configuration path of email identity
     * @param  array    $templateParams
     * @param  int|null $storeId
     * @return Magento\Customer\Model\Customer
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
    {
        try{
            $vendor=$templateParams['vendor'];
            $transportBuilder = $this->transportBuilder;
            $transportBuilder->addTo($vendor->getEmail(), $vendor->getName());
            if($template=="ced_vorders/general/order_new_template"){
            	$transportBuilder->setTemplateIdentifier('ced_csgst_vorders_order_new_template');
            }else{
            	$transportBuilder->setTemplateIdentifier($this->getStoreConfig($template, $storeId));
            }
            $transportBuilder->setTemplateOptions(
                [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
                ]
            );

            $transportBuilder->setTemplateVars($templateParams);
            $transportBuilder->setFrom($this->getStoreConfig($sender, $storeId));
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
        }
        catch(\Exception $e)
        {

        }
        return $this;
    }
}
