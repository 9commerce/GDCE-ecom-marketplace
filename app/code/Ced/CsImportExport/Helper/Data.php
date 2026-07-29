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
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Helper;

/**
 * Class Data
 * @package Ced\CsImportExport\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\State $state
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\State $state,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_helper = $csmarketplaceHelper;
        $this->customerSession = $customerSession;
        $this->state = $state;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->vproductsFactory = $vproductsFactory;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Get vendor ID
     *
     * @return int
     */
    public function getVendorId()
    {
        return $this->_getSession()->getVendorId();
    }

    /**
     * Get vendor
     *
     * @return Ced_CsMarketplace_Model_Vendor
     */
    public function getVendor()
    {
        return $this->_getSession()->getVendor();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAdmin()
    {
        $url = $this->_urlBuilder->getCurrentUrl();

        if ($this->state->getAreaCode() == 'adminhtml') {
            return true;
        }
        if (strpos($url, 'admin') !== false) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAllowedToImport()
    {
        $venod_ids = $this->vproductsFactory->create()->getVendorProductIds($this->getVendorId());
        $storeId = $this->_helper->getStore()->getId();
        $prod_limit = $this->_helper->getStoreConfig('ced_vproducts/general/limit', $storeId);
        if (count($venod_ids) < $prod_limit) {
            return true;
        }
        return false;
    }

    /**
     * @return bool|int|mixed|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function remainLimitToImport()
    {
        $venod_ids = $this->vproductsFactory->create()->getVendorProductIds($this->getVendorId());
        $storeId = $this->_helper->getStore()->getId();
        $prod_limit = $this->_helper->getStoreConfig('ced_vproducts/general/limit', $storeId);
        if (count($venod_ids) < $prod_limit) {
            return $prod_limit - count($venod_ids);
        } else {
            return false;
        }
    }


    /**
     * function sendNotificationMail
     *
     * for sending notification mail to admin for mass import
     *
     * @return Boolean
     */
    public function sendNotificationMail()
    {

        $data1 = [];
        $vendor = $this->getVendor();
        $msg = 'This is notification mail that the vendor ' . $vendor['name'];
        $msg .= ' had run the mass import process for the product. Please review it.';
        $data1['msg'] = $msg;
        $data1['vendor'] = $vendor['name'];
        $data1['sender_email'] = $vendor['name'];
        $data1['sender_name'] = $vendor['email'];
        $mail_recevier = $this->scopeConfig->getValue('ced_csmarketplace/csimportexport/allownotification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data1['receiver_name'] = $this->scopeConfig->getValue('trans_email/ident_' . $mail_recevier . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data1['receiver_email'] = $this->scopeConfig->getValue('trans_email/ident_' . $mail_recevier . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        try {
            $storename = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStore()->getId());
            if (!$storename) {
                $storename = "Default Store";
            }

            $adminname = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_helper->getStore()->getId());
            if (!$adminname) {
                $adminname = "Admin User";
            }

            $adminemail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!$adminemail) {
                $adminemail = "owner@example.com";
            }

            $this->_template = 'ced_csimportexport_notify_admin';
            $this->_inlineTranslation->suspend();
            $this->_transportBuilder->setTemplateIdentifier($this->_template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_helper->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($data1)
                ->setFrom([
                    'name' => $vendor['name'],
                    'email' => $vendor['email'],
                ])
                ->addTo($adminemail, $adminname);

            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {

        }
    }
}
