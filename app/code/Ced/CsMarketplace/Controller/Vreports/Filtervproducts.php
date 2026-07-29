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

namespace Ced\CsMarketplace\Controller\Vreports;

use Ced\CsMarketplace\Model\Session as MarketplaceSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Filtervproducts
 * @package Ced\CsMarketplace\Controller\Vreports
 */
class Filtervproducts extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    public $marketplaceSession;

    /**
     * @var \Laminas\Uri\Uri
     */
    protected $zendUri;

    /**
     * Filtervproducts constructor.
     * @param MarketplaceSession $MarketplaceSession
     * @param \Laminas\Uri\Uri $zendUri
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        MarketplaceSession $MarketplaceSession,
        \Laminas\Uri\Uri $zendUri,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
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
        $this->resultJsonFactory = $jsonFactory;
        $this->marketplaceSession = $MarketplaceSession;
        $this->zendUri = $zendUri;

    }

    /**
     * @return bool|\Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        if (!$this->_getSession()->getVendorId()) {
            return false;
        }

        $params1 = $this->getRequest()->getParams();
        $params1 = current($params1);
        $this->zendUri->setQuery($params1);
        $params = $this->zendUri->getQueryAsArray();
        if (!isset($params['p']) && !isset($params['limit']) && is_array($params)) {
            $this->marketplaceSession->setData('vproducts_reports_filter', $params);
        }

        $navigationBlock = $this->_view->getLayout()->createBlock(\Ced\CsMarketplace\Block\Vendor\Navigation::class);
        if ($navigationBlock) {
            $navigationBlock->setActive('csmarketplace\vreports\vorders');
        }
        $result = $this->resultPageFactory->create(true)->getLayout()
            ->createBlock(\Ced\CsMarketplace\Block\Vreports\Vproducts\ListOrders::class)
            ->setName('csmarketplace_report_vproducts_reports')
            ->setTemplate('Ced_CsMarketplace::vreports/vproducts/list.phtml')->toHtml();

        $resultJson->setData($result);
        return $resultJson;

    }
}
