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
 * Class Chart
 * @package Ced\CsMarketplace\Controller\Vendor
 */
class Chart extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    public $vendor;

    /**
     * @var \Ced\CsMarketplace\Helper\Report
     */
    public $report;

    /**
     * Chart constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMarketplace\Helper\Report $report
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
        \Ced\CsMarketplace\Helper\Report $report
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->vendor = $vendor;
        $this->report = $report;
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
     * Default vendor dashboard page
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $json = [];
        $json['order'] = [];
        $json['xaxis'] = [];

        $json['order']['label'] = __('Orders');
        $json['order']['data'] = [];

        $range = $this->getRequest()->getParam('range', 'day');
        $customerId = $this->_getSession()->getVendorId();

        $vendor = $this->vendor->create()->load($customerId);

        $reportHelper = $this->report;
        if ($vendor && $vendor->getId()) {
            $order = $reportHelper->getChartData($vendor, 'order', $range);

            foreach ($order as $key => $value) {
                $json['order']['data'][] = [$key, $value['total']];
            }
            switch ($range) {
                default:
                case 'day':

                    for ($i = 0; $i < 24; $i++) {
                        $json['xaxis'][] = [$i, $i];
                    }
                    break;
                case 'week':
                    $date_start = strtotime('-' . date('w') . ' days');

                    for ($i = 0; $i < 7; $i++) {
                        $date = date('Y-m-d', $date_start + ($i * 86400));

                        $json['xaxis'][] = [date('w', strtotime($date)), date('D', strtotime($date))];
                    }
                    break;
                case 'month':

                    for ($i = 1; $i <= date('t'); $i++) {
                        $date = date('Y') . '-' . date('m') . '-' . $i;

                        $json['xaxis'][] = [date('j', strtotime($date)), date('d', strtotime($date))];
                    }
                    break;
                case 'year':

                    for ($i = 1; $i <= 12; $i++) {
                        $json['xaxis'][] = [$i, date('M', mktime(0, 0, 0, $i))];
                    }
                    break;
            }
        }

        $resultJson->setData($json);
        return $resultJson;
    }
}
