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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Block\Order\View;

use Magento\Sales\Model\ResourceModel\Order\Item\Collection;

class Items extends \Magento\Sales\Block\Adminhtml\Order\View\Items
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $vproducts;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Items constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Ced\CsMarketplace\Model\Vproducts $vproducts
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->vorders = $vorders;
        $this->vproducts = $vproducts;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
    }

    /**
     * Retrieve required options from parent
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid parent block for this block'));
        }
        $this->setOrder($this->getParentBlock()->getOrder());
        parent::_beforeToHtml();
    }

    /**
     * Retrieve order items collection
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection();
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Ced\CsMarketplace\Model\Vorders
     */
    public function getVorders()
    {
        return $this->vorders;
    }

    /**
     * @return \Ced\CsMarketplace\Model\Vproducts
     */
    public function getVproducts()
    {
        return $this->vproducts;
    }
}
