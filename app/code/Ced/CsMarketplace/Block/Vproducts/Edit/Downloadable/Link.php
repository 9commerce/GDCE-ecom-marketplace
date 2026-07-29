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

namespace Ced\CsMarketplace\Block\Vproducts\Edit\Downloadable;

use Ced\CsMarketplace\Block\Vendor\AbstractBlock;
use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;


/**
 * Class Link
 * @package Ced\CsMarketplace\Block\Vproducts\Edit\Downloadable
 */
class Link extends AbstractBlock
{

    /**
     * @var Data
     */
    public $pricingHelper;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var Type
     */
    protected $type;

    /**
     * Link constructor.
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $coreRegistry
     * @param CurrencyInterface $localeCurrency
     * @param Type $type
     * @param Data $pricingHelper
     */
    public function __construct(
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $coreRegistry,
        CurrencyInterface $localeCurrency,
        Type $type,
        Data $pricingHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_localeCurrency = $localeCurrency;
        $this->type = $type;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @param $_product
     * @return \Magento\Downloadable\Model\Link[]
     */
    public function getDownloadableProductLinks($_product)
    {
        return $this->type->getLinks($_product);
    }

    /**
     * @param $_product
     * @return bool
     */
    public function getDownloadableHasLinks($_product)
    {
        return $this->type->hasLinks($_product);
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * Retrieve curency name by code
     *
     * @param string $code
     * @return string
     */
    public function getCurrencySymbol($code)
    {
        $currency = $this->_localeCurrency->getCurrency($code);
        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }
}
