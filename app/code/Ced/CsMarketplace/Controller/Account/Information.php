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

namespace Ced\CsMarketplace\Controller\Account;

use Magento\Framework\App\Action\Context;

/**
 * Class Information
 * @package Ced\CsMarketplace\Controller\Account
 */
class Information extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Cms\Block\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Information constructor.
     * @param Context $context
     * @param \Ced\CsMarketplace\Helper\Data $data
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     */
    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Helper\Data $data,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        $this->helper = $data;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $jsonFactory;
        $this->_blockFactory = $blockCollectionFactory;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = [];
        $data['story'] = '';
        $data['steps'] = '';
        $data['features'] = '';
        $totalCustomers = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/total_customers',
            $this->getStoreId()
        );
        $totalProducts = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/total_products',
            $this->getStoreId()
        );
        $totalSellers = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/total_sellers',
            $this->getStoreId()
        );
        $storyBlockId = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/our_story_block_id',
            $this->getStoreId()
        );
        $stepsBlockId = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/steps_to_register_block_id',
            $this->getStoreId()
        );
        $featuresBlockId = $this->helper->getStoreConfig(
            'ced_csmarketplace/login_page/features_block_id',
            $this->getStoreId()
        );
        $blockIds = [$storyBlockId, $stepsBlockId, $featuresBlockId];

        $blockCollection = $this->_blockFactory->create();
        $blockCollection->addFieldToFilter('identifier', ['in' => $blockIds]);
        if ($blockCollection->count()) {
            foreach ($blockCollection as $block) {
                $html = $this->_filterProvider->getBlockFilter()->filter($block->getContent());
                if ($block->getIdentifier() == $storyBlockId)
                    $data['story'] = $html;
                if ($block->getIdentifier() == $stepsBlockId)
                    $data['steps'] = $html;
                if ($block->getIdentifier() == $featuresBlockId)
                    $data['features'] = $html;

            }
        }
        $data['total_customers'] = $totalCustomers;
        $data['total_products'] = $totalProducts;
        $data['total_sellers'] = $totalSellers;
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($data);
        return $resultJson;

    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
