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

namespace Ced\CsMarketplace\Controller\Main;
/**
 * Class Info
 * @package Ced\CsMarketplace\Controller\Main
 */
class Info extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Feed
     */
    protected $feedHelper;

    /**
     * Info constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\CsMarketplace\Helper\Feed $feedHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Helper\Feed $feedHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->feedHelper = $feedHelper;
    }


    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $headers = \Magento\Framework\Filesystem\Driver\get_headers();
        $signature = isset($headers['HTTP_X_CEDCOMMERCE_AUTHENTICATION']) ?
            $headers['HTTP_X_CEDCOMMERCE_AUTHENTICATION'] : '';

        if (strlen($signature) > 0 && $signature == '4ec6aa57fd9a8fc7473c8def05e79bd2') {
            $json = [
                        'success' => 1,
                        'information' => $this->feedHelper->getEnvironmentInformation(),
                        'installed_modules' => $this->feedHelper->getCedCommerceExtensions(false, true)
                    ];
            $this->getResponse()->setHeader('HTTP/1.1 200 OK');
            $this->getResponse()->setHeader('HTTP_X_CEDCOMMERCE_AUTHENTICATION', $signature);

            $resultJson->setData($json);
            return $resultJson;

        }
        $this->_forward('noroute');
        return false;
    }
}