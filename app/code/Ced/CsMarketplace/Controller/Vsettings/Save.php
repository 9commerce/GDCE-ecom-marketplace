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

namespace Ced\CsMarketplace\Controller\Vsettings;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsMarketplace\Controller\Vsettings
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vsettingsFactory;

    /**
     * @var mixed
     */
    protected $_serializer;

    /**
     * Save constructor.
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->vsettingsFactory = $vsettingsFactory;
        $this->_serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $section = $this->getRequest()->getParam('section', '');
        $groups = $this->getRequest()->getPost('groups', []);
        if (strlen($section) > 0 && $this->_getSession()->getData('vendor_id') && count($groups) > 0) {
            $vendor_id = (int)$this->_getSession()->getData('vendor_id');
            try {
                foreach ($groups as $code => $values) {
                    foreach ($values as $name => $value) {
                        $serialized = 0;
                        $key = strtolower($section . '/' . $code . '/' . $name);
                        if (is_array($value)) {
                            $value = $this->_serializer->serialize($value);
                            $serialized = 1;
                        }
                        $setting = $this->vsettingsFactory->create()
                            ->loadByField(['key', 'vendor_id'], [$key, $vendor_id]);

                        if ($setting && $setting->getId()) {
                            $setting->setVendorId($vendor_id)
                                ->setGroup($section)
                                ->setKey($key)
                                ->setValue($value)
                                ->setSerialized($serialized)
                                ->save();
                        } else {

                            $setting = $this->vsettingsFactory->create();
                            $setting->setVendorId($vendor_id)
                                ->setGroup($section)
                                ->setKey($key)
                                ->setValue($value)
                                ->setSerialized($serialized)
                                ->save();
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(__('The setting information has been saved.'));
                $this->_redirect('*/*');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*');
                return;
            }
        }
        $this->_redirect('*/*');
    }
}