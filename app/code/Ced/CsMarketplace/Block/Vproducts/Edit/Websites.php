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

namespace Ced\CsMarketplace\Block\Vproducts\Edit;

/**
 * Class Websites
 * @package Ced\CsMarketplace\Block\Vproducts\Edit
 */
class Websites extends \Ced\CsMarketplace\Block\Vproducts\Store\Switcher
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $marketplaceSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Websites constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Store\Model\Website $website
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Ced\CsMarketplace\Model\Session $marketplaceSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\Website $website,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Ced\CsMarketplace\Model\Session $marketplaceSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $website, $websiteFactory, $vproducts, $registry, $groupFactory, $data);
        $this->setTemplate('Ced_CsMarketplace::vproducts/edit/websites.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->registry = $registry;
        $this->marketplaceSession = $marketplaceSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    /**
     * Retrieve edited product model instance
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return array|mixed
     */
    public function getWebsites()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * @param $websiteId
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        $websiteIds = $this->getProduct()->getWebsiteIds();
        $customerSession = $this->marketplaceSession->getCustomerSession();
        if (!$this->getProduct()->getId() && $customerSession->getFormError() == true) {
            $productformdata = $customerSession->getProductFormData();
            $websiteIds =
                isset($productformdata['product']['website_ids']) ? $productformdata['product']['website_ids'] : [];
        }elseif (!$this->getProduct()->getId()){
            $websiteIds = [$this->getDefaultWebsiteId()];
        }
        return in_array($websiteId, $websiteIds);
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getWebsitesReadonly();
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getStoreName($storeId)
    {
        return $this->storeManager->getStore($storeId)->getName();
    }
    /**
     * @return string|int
     */
    public function getDefaultWebsiteId()
    {
        return $this->storeManager->getDefaultStoreView()->getWebsiteId();
    }
}
