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

namespace Ced\CsPurchaseOrder\Helper;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\UrlInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class Data
 * @package Ced\CsPurchaseOrder\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $custmoersession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categorycollection;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    protected $categoryCollection;

    protected $attachmentsFactory;

    protected $vendorFactory;

    protected $url;

    protected $_transportBuilder;

    protected $purchaseorder;

    protected $customerRepository;

    /**
     * Data constructor.
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param \Ced\CsPurchaseOrder\Model\AttachmentsFactory $attachmentsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param UrlInterface $url
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categorycollection
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Ced\CsPurchaseOrder\Model\AttachmentsFactory $attachmentsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        UrlInterface $url,
        TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categorycollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder $purchaseorder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Helper\Context $context
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->custmoersession = $session;
        $this->_inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->pricingHelper = $pricingHelper;
        $this->categoryCollection = $categoryCollection;
        $this->attachmentsFactory = $attachmentsFactory;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->vendorFactory = $vendorFactory;
        $this->url = $url;
        $this->_transportBuilder = $transportBuilder;
        $this->categorycollection = $categorycollection;
        $this->_categoryFactory = $categoryFactory;
        $this->purchaseorder = $purchaseorder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function uploadDocument($locate, $quoteid, $vendorid)
    {
        $data = [];
        if ($locate == 'customer') {

            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $path = $mediaDirectory->getAbsolutePath('cspurchaseorder/files/' . $this->custmoersession
                    ->getCustomerId());
            $imagePath = false;
            $url = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/files/' . $this
                    ->custmoersession->getCustomerId() . '/';
        } else {
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $path = $mediaDirectory->getAbsolutePath('cspurchaseorder/vendor/' . $quoteid . '/' . $this
                    ->custmoersession->getCustomerId() . '/' . $vendorid);
            $imagePath = false;
            $url = $this->_storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/vendor/' . $quoteid . '/' . $this
                    ->custmoersession->getCustomerId() . '/' . $vendorid . '/';
        }
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => "document_file"]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'pdf', 'docx', 'doc', 'zip', 'txt', 'odt']);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $fileData = $uploader->validateFile();
            $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            $flag = $uploader->save($path, $fileData['name']);
            $imagePath = true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $data['file_path'] = $url;
        $data['file_name'] = str_replace(' ', '_', $fileData['name']);
        $data['file_type'] = $extension;
        return $data;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function getFormatedPrice($price)
    {

        $priceHelper = $this->pricingHelper;
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue('ced_purchaseorder/general/activation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $config_path
     * @return mixed
     */
    public function getConfigData($config_path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue($config_path, $storeScope);
    }

    /**
     * @param $vendorId
     * @return array|bool
     */
    public function getVendorQuotations($vendorId)
    {

        if ($vendorId) {
            $category_ids = $this->categoryCollection->create()->addFieldToFilter('vendor_id', $vendorId)
                ->getColumnValues('category_id');
            return $category_ids;
        }
        return false;
    }

    /**
     * @param $requestid
     * @return mixed
     */
    public Function getFileSrc($requestid)
    {
        if ($requestid) {
            $attachment = $this
                ->attachmentsFactory->create()->load($requestid, 'c_quote_id');

            return $attachment->getFilePath();
        }
    }

    /**
     * @return mixed
     */
    public Function getFileName($requestid)
    {
        if ($requestid) {
            $attachment = $this
                ->attachmentsFactory->create()->load($requestid, 'c_quote_id');

            return $attachment->getFileName();
        }
    }

    /**
     * @param $requestid
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmailToCustomer($requestid)
    {
        $emailvariables['customername'] = $this->custmoersession->getCustomer()->getName();
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);
        $emailvariables['quote_id'] = $this->url
            ->getUrl('cspurchaseorder/request/edit', ['requestid' => $requestid]);

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";
        $this->_template = 'ced_purchaseorder_quote_customer_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo($this->custmoersession->getCustomer()->getEmail());
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $requestid
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmailToVendors($requestid)
    {
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";

        $emails = $this->getVendors($requestid);
        if($adminemail){
            array_push($emails, $adminemail);
        }

        $this->_template = 'ced_purchaseorder_quote_vendor_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo($emails);

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $requestid
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendApprovedEmailToCustomer($requestid)
    {
        $customer = $this->getCustomer($requestid);
        $emailvariables['customername'] = $customer->getFirstname();
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);
        $emailvariables['quote_id'] = $this->url->getUrl('cspurchaseorder/request/view');

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";

        $this->_template = 'ced_purchaseorder_approved_quote_customer_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo($customer->getEmail());
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $requestid
     * @param $vendorId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendApprovedEmailToVendors($requestid, $vendorId)
    {
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";

        $vendorMail = $this->getVendorEmail($vendorId);
        if (!$vendorMail)
            $vendorMail = "vendor@example.com";

        $this->_template = 'ced_purchaseorder_approved_quote_vendor_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo([$vendorMail, $adminemail]);

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {

        }
    }

    public function sendUpdateEmailToCustomer($requestid)
    {
        $customer = $this->getCustomer($requestid);
        $emailvariables['customername'] = $customer->getFirstname();
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);
        $emailvariables['quote_id'] = $this->url
            ->getUrl('cspurchaseorder/request/edit', ['requestid' => $requestid]);

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";

        $this->_template = 'ced_purchaseorder_updated_quote_customer_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo($customer->getEmail());
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
        }
    }

    public function sendUpdateEmailToVendors($requestid, $vendorId)
    {
        $emailvariables['quote_name'] = sprintf("%'.09d", $requestid);

        $storename = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$storename)
            $storename = "Default Store";

        $emailvariables['storename'] = $storename;

        $adminname = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId());
        if (!$adminname)
            $adminname = "Admin User";

        $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$adminemail)
            $adminemail = "owner@example.com";

        $vendorMail = $this->getVendorEmail($vendorId);
        if (!$vendorMail)
            $vendorMail = "vendor@example.com";

        $this->_template = 'ced_purchaseorder_updated_quote_vendor_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $adminname,
                'email' => $adminemail,
            ])
            ->addTo([$vendorMail, $adminemail]);

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {

        }
    }

    /**
     * @param $requestid
     * @return array
     */
    public function getVendors($requestid)
    {
        $vendorIds = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $requestid)
            ->getColumnValues('vendor_id');
        $vendorEmails = [];
        foreach ($vendorIds as $vendorId) {
            $vendorEmail = $this->getVendorEmail($vendorId);
            if(!empty($vendorEmail))
                array_push($vendorEmails, $vendorEmail);

        }
        return $vendorEmails;
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getAssignedVendors($categoryId)
    {
        return $this->categoryCollection->create()->addFieldToFilter('category_id', $categoryId)
            ->getColumnValues('vendor_id');
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorEmail($vendorId)
    {
        return $this->vendorFactory->create()->load($vendorId)->getEmail();
    }

    /**
     * @param $catId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryName($catId)
    {
        $pathName = [];
        if ($catId) {
            $pathIds = $this->_categoryFactory->create()->load($catId)->getPathIds();
            unset($pathIds[0]);
            unset($pathIds[1]);
            foreach ($pathIds as $pathId) {
                $catModel = $this->categorycollection->create()->addAttributeToSelect('*');
                $catModel->addAttributeToFilter('entity_id', $pathId)->getFirstItem();
                foreach ($catModel as $cat) {
                    $pathName[] = $cat->getName();
                }
            }

            $leave = implode('->', $pathName);
            return $leave;
        }
    }

    public function getCustomer($requestid){
        $purchaseorder = $this->purchaseorderFactory->create();
        $this->purchaseorder->load($purchaseorder,$requestid);

        if($purchaseorder->getCustomerId())
         return $this->customerRepository->getById($purchaseorder->getCustomerId());

        return false;
    }

    public function customerLogin()
    {
        return $this->custmoersession->isLoggedIn();
    }
}
