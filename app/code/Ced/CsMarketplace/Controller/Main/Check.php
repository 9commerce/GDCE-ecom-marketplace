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
 * Class Check
 * @package Ced\CsMarketplace\Controller\Main
 */
class Check extends \Magento\Framework\App\Action\Action
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
     * Check constructor.
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
        $data = $this->getRequest()->getParams();
        $json = ['success' => 0, 'module_name' => '', 'module_license' => ''];
        if ($data && isset($data['module_name'])) {
            $json['module_name'] = strtolower($data['module_name']);
            $json['module_license'] = $this->feedHelper
                ->getStoreConfig(\Ced\CsMarketplace\Block\Extensions::HASH_PATH_PREFIX .
                    strtolower($data['module_name']));
            if (strlen($json['module_license']) > 0) $json['success'] = 1;

            $resultJson->setData($json);
            return $resultJson;
        }
        $this->_forward('noroute');
        return false;
    }
}