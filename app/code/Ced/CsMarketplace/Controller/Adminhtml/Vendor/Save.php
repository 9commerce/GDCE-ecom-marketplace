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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vendor;

use Magento\Backend\App\Action;
use Ced\CsMarketplace\Model\VproductsFactory;

/**
 * Class Save
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vendor
 */
class Save extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $aclHelper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;


    /**
     * @var \Ced\CsMarketplace\Model\VshopFactory
     */
    protected $vshopFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Mail
     */
    private $marketplaceMailHelper;

    /**
     * @var VproductsFactory
     */
    private $vProductsFactory;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Magento\Backend\Model\Session $session
     * @param \Ced\CsMarketplace\Helper\Mail $marketplaceMailHelper
     * @param VproductsFactory $vProductsFactory
     */
    public function __construct(
        Action\Context $context,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Magento\Backend\Model\Session $session,
        \Ced\CsMarketplace\Helper\Mail $marketplaceMailHelper,
        VproductsFactory $vProductsFactory
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->registry = $registry;
        $this->customerFactory = $customerFactory;
        $this->aclHelper = $aclHelper;
        $this->session = $session;

        $this->marketplaceMailHelper = $marketplaceMailHelper;
        $this->vProductsFactory = $vProductsFactory;
        parent::__construct($context);
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $sendNotification = false;
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->vendorFactory->create();
            $registry = $this->registry;
            $customerid = isset($data['vendor']['customer_id']) && (int)$data['vendor']['customer_id'] ?
                (int)$data['vendor']['customer_id'] : 0;
            if ($id = $this->getRequest()->getParam('vendor_id')) {
                $this->registry->register('data_com', $this->getRequest()->getParam('vendor_id'));
                if ($id = $this->getRequest()->getParam('vendor_id')) {

                    if ($registry->registry('ven_id')) {
                        $registry->unregister('ven_id');
                    }
                    $registry->register('ven_id', $id);
                }
                $model->load($id);
                if ($model && $model->getId()) {
                    $customerId = (int)$model->getCustomerId();
                    if (isset($data['vendor']['customer_id'])) {
                        unset($data['vendor']['customer_id']);
                    }
                }
            }
            $customer = $this->customerFactory->create()->load($customerid);
            if ($customer && $customer->getId()) {
                $data['vendor']['email'] = $customer->getEmail();
            }

            $vendorData = array_merge($this->aclHelper->getDefultAclValues(), $data['vendor']);


            if(isset($data['vendor']['status']) && $data['vendor']['status'] !=$model->getStatus()){
                $sendNotification = true;
            }
            $model->addData($vendorData);

            try {
                if ($model->validate()) {
                    $this->_eventManager->dispatch('ced_csmarketplace_custom_vendor_save', [
                        'current' => $this,
                        'action' => $this,
                    ]);
                    $model->save();

                    if($sendNotification){

                            //$this->marketplaceMailHelper->sendAccountEmail($model->getStatus(),$model);
                            //$this->marketplaceMailHelper->sendAccountEmailToAdmin($model);

                            $this->vProductsFactory->create()
                                ->changeProductsStatus(array($model->getId()), $model->getStatus());

                    }

                } elseif ($model->getErrors()) {
                    foreach ($model->getErrors() as $error) {
                        $this->messageManager->addErrorMessage($error);
                    }
                    $this->session->setFormData($data);
                    $this->_redirect('*/*/edit', ['vendor_id' => $model->getId()]);
                    return;
                }
                $this->messageManager->addSuccessMessage(__('Vendor is successfully saved'));
                $this->session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['vendor_id' => $model->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->session->setFormData($data);
                $this->_redirect('*/*/edit', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find vendor to save'));
        $this->_redirect('*/*/');
    }
}
