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

namespace Ced\CsMarketplace\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsMarketplace\Controller\Vendor
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->vendor = $vendor;
        $this->registry = $registry;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * Default vendor profile page
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return $this->_redirect('*/account/login');
        }
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/profile');
        }
        if ($data = $this->getRequest()->getPost()) {
            $model = $this->vendor->create();
            $this->registry->register('data_com', $this->getRequest()->getParam('vendor_id'));
            if ($id = $this->_getSession()->getVendorId()) {
                $model->load($id);
                if (isset($data['vendor'])) {
                    $model->addData($data['vendor']);
                    try {
                        if ($model->validate()) {
                            $model->extractNonEditableData();
                            $model->save();
                            $customer = $this->_getSession()->getCustomer();
                            $dataPost = $this->getRequest()->getParams();
                            $vendorData = $dataPost['vendor'];
                            if (array_key_exists('current_password', $vendorData)) {
                                $currPass = $dataPost['vendor']['current_password'];
                                $newPass = $dataPost['vendor']['new_password'];
                                $confPass = $dataPost['vendor']['confirm_password'];

                                $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();

                                list($_salt, $salt) = explode(':', $oldPass);
                                if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                                    if (strlen($newPass)) {
                                        $customer->setPassword($newPass);
                                        $customer->setPasswordConfirmation($confPass);
                                        $customer->save();
                                    } else {
                                        $this->messageManager->addErrorMessage(__('New password field cannot be empty.'));
                                        $this->_getSession()->setFormData($data);
                                        return $this->_redirect('*/*/profile');
                                    }
                                } else {
                                    $this->messageManager->addErrorMessage(__('Invalid current password'));
                                    $this->_getSession()->setFormData($data);
                                    return $this->_redirect('*/*/profile');
                                }
                            }
                        } elseif ($model->getErrors()) {
                            foreach ($model->getErrors() as $error) {
                                $this->messageManager->addErrorMessage($error);
                            }
                            $this->_getSession()->setFormData($data);
                            return $this->_redirect('*/*/profile');
                        }
                        $this->_getSession()->setVendor($model->getData());
                        $this->messageManager->addSuccessMessage(__('The profile information has been saved.'));
                        return $this->_redirect('*/*/profileview');
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        return $this->_redirect('*/*/profile');
                    }
                }
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find vendor to save'));
        return $this->_redirect('*/*/profile');
    }
}
