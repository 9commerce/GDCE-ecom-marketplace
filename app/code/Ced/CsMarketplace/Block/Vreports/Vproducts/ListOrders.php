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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Block\Vreports\Vproducts;

use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Block\Vproducts\Store\Switcher;
use Ced\CsMarketplace\Helper\Report;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class ListOrders
 * @package Ced\CsMarketplace\Block\Vreports\Vproducts
 */
class ListOrders extends AbstractBlock
{

    /**
     * @var
     */
    public $_storeManager;

    /**
     * @var
     */
    protected $_filterCollection;

    /**
     * @var Report
     */
    protected $reportHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
     */
    protected $_filtercollection;

    /**
     * ListOrders constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Switcher $_storeSwitcher
     * @param Report $reportHelper
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Switcher $_storeSwitcher,
        Report $reportHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->reportHelper = $reportHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $reportHelper = $this->reportHelper;
        $params = $this->session->getData('vproducts_reports_filter');

        if (isset($params) && $params != null) {
            if(!isset($params['website_id']) || $params['website_id'] === null) {
                $params['website_id'] = $this->_storeManager->getStore()->getWebsiteId();
            }
            $productsCollection = $reportHelper->getVproductsReportModel(
                $this->getVendor()->getId(),
                $params['from'],
                $params['to'],
                true,
                $params['website_id']
            );

            $this->_filtercollection = $productsCollection;
            $this->setVproductsReports($this->_filtercollection);
        } else {
            $this->setVproductsReports([]);
        }
    }

    /**
     * @param $amount
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param null $currency
     * @return float
     */
    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $currency = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        return $this->priceCurrency->format(
            $amount,
            $includeContainer,
            $precision,
            $scope,
            $currency
        );
    }

    /**
     * Get all active websites
     * @return array
     */
    public function getWebsites(){
        $websites = $this->_storeManager->getWebsites();
        $websiteArray = array();
        foreach($websites as $website){
            $websiteArray[$website->getId()] = $website->getName();
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
