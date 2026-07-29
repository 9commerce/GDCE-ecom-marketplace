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

namespace Ced\CsMarketplace\Controller\Vpayments;

/**
 * Class Filter
 * @package Ced\CsMarketplace\Controller\Vpayments
 */
class Filter extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Framework\UrlFactory $urlFactory,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
                                \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
                                \Ced\CsMarketplace\Helper\Acl $aclHelper,
                                \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {

        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory,
            $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * @return \Ced\CsMarketplace\Controller\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $reset_filter = $this->getRequest()->getParam('reset_filter');
        $params = $this->getRequest()->getParams();
        if ($reset_filter == 1)
            $this->_getSession()->uns('payment_filter');
        else if (!isset($params['p']) && !isset($params['limit']) && is_array($params)) {
            $this->_getSession()->setData('payment_filter', $params);
        }
        $block = $this->_view->getLayout()
                ->createBlock('Ced\CsMarketplace\Block\Vpayments\Stats')
                ->setTemplate('Ced_CsMarketplace::vpayments/stats.phtml')
                ->setTemplateId('fiter-stats')->toHtml() . $this->_view->getLayout()
                ->createBlock('Ced\CsMarketplace\Block\Vpayments\ListBlock')
                ->setTemplate('Ced_CsMarketplace::vpayments/list.phtml')
                ->setTemplateId('fiter-list')->toHtml();

        $this->getResponse()->setBody($block);
    }
}
