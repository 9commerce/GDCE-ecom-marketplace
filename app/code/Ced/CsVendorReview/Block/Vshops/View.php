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

namespace Ced\CsVendorReview\Block\Vshops;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Review\Model\Rating
     */
    protected $rating;

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory
     */
    protected $vproducts;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var \Ced\CsVendorReview\Helper\Data
     */
    protected $helper;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * View constructor.
     * @param \Ced\CsVendorReview\Helper\Data $
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\Rating $rating
     * @param \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproducts
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Ced\CsVendorReview\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\Rating $rating,
        \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory $vproducts,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->rating = $rating;
        $this->vproducts = $vproducts;
        $this->reviewCollection = $reviewCollection;
        $this->ratingCollection = $ratingCollection;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        if ($this->scopeConfig->getValue('ced_csmarketplace/vendorreview/activation')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor) {
            $this->_vendor = $this->registry->registry('current_vendor');
        }
        return $this->_vendor;
    }

    /**
     * @return bool|float
     */
    public function getVendorRating()
    {
        $review_data = $this->reviewCollection->create()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
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
     * @return array|bool
     */
    public function getRatingDetails()
    {
        $review_data = $this->reviewCollection->create()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->addFieldToFilter('status', 1);

        $rating = $this->ratingCollection->create()
            ->addFieldToSelect('rating_code')
            ->addFieldToSelect('rating_label')
            ->addFieldToSelect('sort_order')
            ->setOrder('sort_order', 'ASC');
        $rating_details = [];

        foreach ($review_data as $key => $value) {
            foreach ($rating as $k => $val) {
                if ($value[$val['rating_code']] > 0) {
                    if (isset($rating_details[$val['rating_label']]['rating'])) {
                        $rating_details[$val['rating_label']]['rating'] =
                            $rating_details[$val['rating_label']]['rating'] + $value[$val['rating_code']];
                    } else {
                        $rating_details[$val['rating_label']]['rating'] = $value[$val['rating_code']];
                    }
                    if (isset($rating_details[$val['rating_label']]['count'])) {
                        $rating_details[$val['rating_label']]['count'] =
                            $rating_details[$val['rating_label']]['count'] + 1;
                    } else {
                        $rating_details[$val['rating_label']]['count'] = 1;
                    }
                }
            }
        }

        if (!empty($rating_details)) {
            return $rating_details;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->getVendor()->getId();
    }

    /**
     * @param $vendor_products
     * @return float|int
     */
    public function getProductRating($vendor_products)
    {
        $rating_sum = 0;
        foreach ($vendor_products as $product) {
            $rating = $this->rating->getEntitySummary($product['product_id']);
            if ($rating->getSum() != null) {
                $rating_sum += ($rating->getSum() / $rating->getCount());
            }
        }
        return $rating_sum;
    }

    /**
     * @param $vendor_id
     * @return mixed
     */
    public function getVendorProducts($vendor_id)
    {
        $products = $this->vproducts->create()
            ->addFieldToFilter('vendor_id', $vendor_id)
            ->addFieldToFilter('check_status', 1)
            ->addFieldToSelect('product_id');
        return $products->getData();
    }

    /**
     * @return bool
     */
    public function isCustomerReviewPage()
    {
        $route = $this->request->getRouteName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();

        return ($route == "csvendorreview" && $controller == "rating" && $action == "lists")? true : false;
    }

    /**
     * @return \Ced\CsVendorReview\Helper\Data
     */
    public function helper()
    {
        return $this->helper;
    }

    /**
     * @return int|string
     */
    public function getAvgRating()
    {
        $reviewCollection = $this->reviewCollection->create()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->addFieldToFilter('status', 1);
        $ratingCollection = $this->ratingCollection->create()
            ->addFieldToSelect('rating_code');
        if ($ratingCollection->getSize() > 0) {
            $rating_sum = 0;
            foreach ($reviewCollection as $key => $value) {
                foreach ($ratingCollection as $k => $val) {
                    if ($value[$val['rating_code']] > 0) {
                        $rating_sum += $value[$val['rating_code']];
                    }
                }
            }
            return number_format(($rating_sum/$ratingCollection->getSize())*0.05, 1, ".", ",");
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getSellerRatingsCount(): int
    {
        $reviews = $this->reviewCollection->create()
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->addFieldToFilter('status', 1);
        return $reviews->getSize();
    }
}
