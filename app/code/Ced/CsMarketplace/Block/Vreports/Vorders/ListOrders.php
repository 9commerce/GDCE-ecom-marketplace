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

namespace Ced\CsMarketplace\Block\Vreports\Vorders;

use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Helper\Report;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ListOrders
 * @package Ced\CsMarketplace\Block\Vreports\Vorders
 */
class ListOrders extends AbstractBlock
{

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var array|bool
     */
    protected $_filtercollection;

    /**
     * @var Report
     */
    protected $reportHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Set the Vendor object and Vendor Id in customer session
     * ListOrders constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Report $reportHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Report $reportHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->reportHelper = $reportHelper;
        $this->priceCurrency = $priceCurrency;
        $this->customerSession = $customerSession;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $reportHelper = $this->reportHelper;
        $params = $this->session->getData('vorders_reports_filter');

        if (isset($params) && $params != null) {
            $ordersCollection = $reportHelper->getVordersReportModel(
                $this->getVendor(),
                $params['period'],
                $params['from'],
                $params['to'],
                $params['payment_state'],
                $params['website_id']
            );

            if (count($ordersCollection) > 0) {
                $this->_filtercollection = $ordersCollection;
                $this->setVordersReports($this->_filtercollection);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCustomCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vorders.export.grid.pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
                ->setShowPerPage(true)->setCollection(
                    $this->getCustomCollection()
                )->setData('path', 'csmarketplace/vreports/vorders');
            $this->setChild('pager', $pager);
            $this->getCustomCollection()->load();
        }
        return $this;
    }

    public function getCustomCollection()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
        $collection = $this->getVordersReports();
        if ($collection) {
            $collection->setPageSize($pageSize);
            $collection->setCurPage($page);
        }
        return $collection;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    /**
     * @param $amounts
     * @param bool $includeContainer
     * @param int $precision
     * @param null $scope
     * @param null $currency
     * @return float
     */
    public function formatCurrency(
        $amounts,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $currency = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        return $this->priceCurrency->format(
            $amounts,
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
