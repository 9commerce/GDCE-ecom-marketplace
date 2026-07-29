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
 * @package     Ced_Custom
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vreports\Vorders;

use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class FilterOrders extends AbstractBlock
{

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var Session
     */
    public Session $customerSession;

    /**
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Get all active websites
     * @return array
     */
    public function getWebsites()
    {
        $websites = $this->_storeManager->getWebsites();
        $websiteArray = [];
        foreach ($websites as $websiteData) {
            $websiteArray[$websiteData->getId()] = $websiteData->getName();
        }
        return $websiteArray;
    }

    /**
     * Get default website id
     * @return int
     */
    public function getDefaultWebsiteId()
    {
        return $this->_storeManager->getDefaultStoreView()->getWebsiteId();
    }
}
