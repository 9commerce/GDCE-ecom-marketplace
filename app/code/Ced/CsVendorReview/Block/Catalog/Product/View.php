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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Block\Catalog\Product;


use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Product View block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $_vproducts;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * @var \Ced\CsVendorReview\Helper\Data
     */
    protected $helper;

    /**
     * View constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Ced\CsVendorReview\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Ced\CsVendorReview\Helper\Data $helper,
        array $data = []
    ) {
        $this->_vendorFactory = $vendorFactory;
        $this->_vproducts = $vproductsFactory;
        $this->reviewCollection = $reviewCollection;
        $this->ratingCollection = $ratingCollection;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return bool|float
     */
    public function getVendorRating()
    {
        $_product = $this->getProduct();
        $vendorId = $this->_vproducts->create()->getVendorIdByProduct($_product->getId());
        $review_data = $this->reviewCollection->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', 1);

        $rating = $this->ratingCollection->create()
            ->addFieldToSelect('rating_code');
        $count = 0;
        $rating_sum = 0;

        foreach ($review_data as $key => $value) {
            foreach ($rating as $k => $val) {
                if ($value[$val['rating_code']] > 0) {
                    $rating_sum += $value[$val['rating_code']];
                    $count++;
                }
            }
        }

        if ($count > 0 && $rating_sum > 0) {
            $width = $rating_sum / $count;
            return ceil($width);
        } else {
            return false;
        }
    }

    /**
     * @return \Ced\CsVendorReview\Helper\Data
     */
    public function helper()
    {
        return $this->helper;
    }
}
