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


use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Sample
 * @package Ced\CsMarketplace\Block\Vproducts\Edit\Downloadable
 */
class Sample extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{

    /**
     * @var \Magento\Downloadable\Model\Product\Type
     */
    protected $type;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Sample constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Downloadable\Model\Product\Type $type
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Downloadable\Model\Product\Type $type,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->type = $type;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * @param $_product
     * @return \Magento\Downloadable\Model\ResourceModel\Sample\Collection
     */
    public function getDownloadableProductSamples($_product)
    {
        return $this->type->getSamples($_product);
    }

    /**
     * @param $_product
     * @return bool
     */
    public function getDownloadableHasSamples($_product)
    {
        return $this->type->hasSamples($_product);
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }
}
