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

class ListBlock extends \Ced\CsMarketplace\Block\Vshops\ListBlock
{
    /**
     * @var \Magento\Review\Model\Rating
     */
    protected $rating;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * ListBlock constructor.
     * @param \Magento\Review\Model\Rating $rating
     * @param \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Product\ProductList $prodListHelper
     * @param \Ced\CsMarketplace\Helper\Tool\Image $imageHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Magento\Tax\Helper\Data $magentoTaxHelper
     * @param \Magento\Directory\Helper\Data $magentoDirectoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Review\Model\Rating $rating,
        \Ced\CsVendorReview\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Ced\CsVendorReview\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Ced\CsMarketplace\Model\Vshop $vshop,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Product\ProductList $prodListHelper,
        \Ced\CsMarketplace\Helper\Tool\Image $imageHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Magento\Tax\Helper\Data $magentoTaxHelper,
        \Magento\Directory\Helper\Data $magentoDirectoryHelper,
        array $data = []
    ) {
        $this->rating = $rating;
        $this->reviewCollection = $reviewCollection;
        $this->ratingCollection = $ratingCollection;
        parent::__construct(
            $imageHelper,
            $aclHelper,
            $magentoTaxHelper,
            $magentoDirectoryHelper,
            $layerResolver,
            $urlHelper,
            $vshop,
            $vendor,
            $csmarketplaceHelper,
            $prodListHelper,
            $context,
            $data
        );
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
     * @param $vendor
     * @return string
     */
    public function getReviewsSummaryHtml($vendor)
    {
        if ($this->_scopeConfig->getValue('ced_csmarketplace/vendorreview/activation')) {
            $review_data = $this->reviewCollection->create()
                ->addFieldToFilter('vendor_id', $vendor->getId())
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
                $width = number_format(($rating_sum/$count)*0.05, 1, ".", ",");
                return '<div class="rating-summary">
							 <div class="vendor-rating-result">
								 <strong><span>'.$width .'</span></strong>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13"
                     height="12" viewBox="0 0 13 12" fill="none">
                    <rect x="0.445801" width="12" height="12" fill="url(#pattern0)"/>
                    <defs>
                        <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                            <use xlink:href="#image0_130_227" transform="scale(0.0416667)"/>
                        </pattern>
                        <image id="image0_130_227" width="24" height="24"
                               xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYEAYAAACw5+G7AAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAAGYktHRAAAAAAAAPlDu38AAAAJcEhZcwAAAGAAAABgAPBrQs8AAAAHdElNRQfnAg0GBRFcdaIiAAADm0lEQVRYw82Xb0hddRjHP89Rz1lNnGOZd7VEcQPJtvtGp9e73JyDvViXG5EzGIX4QtzGxojm6kVZo6herIIMlvQikI1mgsRoQdwlN52xyAvOFGnUQDau9969GObK7tx5euHurji3e+rem33fnMM5z/n+ec6P55yfkGGofbEcCgqQuSfNd8+dA7F5SBW1JuNH9uwRo+ZXmJnJtG7mAmjwM/OVY8dUg0HLUl10fMQ83tGRaT3JnPHeXsjJgeJCM3b5MmDJhrKyRSW9+tPUFGp3xTeXl4vRIDA/n66ukbleFF816/3+ZYwnsFeqSkoQ+dF8zufLlGrmAqhc5OihQw4kvRxwUucMaS8h1aGOvNnKSridbzw2NgZs5y9Jzati2u+73WLUe24duXTp3+pn4A3Yn8v5w4cdG0+2zi9lBw+mqy6qqmAYMJRvuioqwL4qZ1wukKfsK+vXA5vFW1QE+qn0uVzAN3rF5QIqpa+oCMjHvXMnMMPwqlX/QLuAurk5VJ7hh4EBRF/Xl6JRkPOyLhIBftfWcBgY0wuxGOjPRlk4DMYGbZ6ehm2z8enJSVE7uM8MBQIIbeJpbEz/jfxHUOnR0UDAAB5l07JT4/8N4bRUlpYaiDyhlU1NQERPh8Mr7csBEj7fth9vbjZE6l++NRUKwfwfOaHqauA19o2OrrTLZfAd7okJdH6X8YnHk/B9z9RQHVAoLERzXjDP9vcjekCe37FjBY3/pq8OD6N2i+nz+8VokNmq69cTN+8ZoyINAjduwLrbcd/u3cCI1pw6tQLGX+RoXx/YLfG3GhuXGr/rNxXLwpgVgcFi86uTJ0G/lL1tbdnzLU3a290NT0fi/vZ2ERFQvV91yg9ZkkBbpDQazZ7xBOxr4o5EUhl3HGBRlA805vVm3b/K13rNuU7KAGoPKOTmgo5JT3V11gMg7fJxTU1SN80ASO5QXp7bDUT5Ij8/+/71DP2rVyM5H+aVbNmSqjxlQrAto6K2FviTXxxZOE6BbQOiZ7u7F11X8bW1AW8wYzhYurrNWOPxLJyHQverckAk7/CeozXZyZsjI2AHjDVer8j2QLx2//7EEXjY3lhVRWKup8YmuurqHLXsgX3QYKe1dXx8mT1uj/lRNKoa9FkTra3JcZuKb+HvN/ncXZ6l/J3W1vHx9APY3++yGk+cUA36rGdv3lQNfmvGurpUBwdh7dr0G7TAk+S9o3NHN+0ASzuXMcIH6jjfGP0NXD2f3hocCJUAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjMtMDItMTNUMDY6MDU6MTcrMDA6MDCg/5h7AAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIzLTAyLTEzVDA2OjA1OjE3KzAwOjAw0aIgxwAAACh0RVh0ZGF0ZTp0aW1lc3RhbXAAMjAyMy0wMi0xM1QwNjowNToxNyswMDowMIa3ARgAAAAASUVORK5CYII="/>
                    </defs>
                </svg>
							 </div>
							</div>';
            } else {
                $width = number_format(0, 1, ".", ",");
                return '<div class="rating-summary">
							 <div class="vendor-rating-result">
								 <strong><span>'.$width .'</span></strong>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="13"
                     height="12" viewBox="0 0 13 12" fill="none">
                    <rect x="0.445801" width="12" height="12" fill="url(#pattern0)"/>
                    <defs>
                        <pattern id="pattern0" patternContentUnits="objectBoundingBox" width="1" height="1">
                            <use xlink:href="#image0_130_227" transform="scale(0.0416667)"/>
                        </pattern>
                        <image id="image0_130_227" width="24" height="24"
                               xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYEAYAAACw5+G7AAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAAGYktHRAAAAAAAAPlDu38AAAAJcEhZcwAAAGAAAABgAPBrQs8AAAAHdElNRQfnAg0GBRFcdaIiAAADm0lEQVRYw82Xb0hddRjHP89Rz1lNnGOZd7VEcQPJtvtGp9e73JyDvViXG5EzGIX4QtzGxojm6kVZo6herIIMlvQikI1mgsRoQdwlN52xyAvOFGnUQDau9969GObK7tx5euHurji3e+rem33fnMM5z/n+ec6P55yfkGGofbEcCgqQuSfNd8+dA7F5SBW1JuNH9uwRo+ZXmJnJtG7mAmjwM/OVY8dUg0HLUl10fMQ83tGRaT3JnPHeXsjJgeJCM3b5MmDJhrKyRSW9+tPUFGp3xTeXl4vRIDA/n66ukbleFF816/3+ZYwnsFeqSkoQ+dF8zufLlGrmAqhc5OihQw4kvRxwUucMaS8h1aGOvNnKSridbzw2NgZs5y9Jzati2u+73WLUe24duXTp3+pn4A3Yn8v5w4cdG0+2zi9lBw+mqy6qqmAYMJRvuioqwL4qZ1wukKfsK+vXA5vFW1QE+qn0uVzAN3rF5QIqpa+oCMjHvXMnMMPwqlX/QLuAurk5VJ7hh4EBRF/Xl6JRkPOyLhIBftfWcBgY0wuxGOjPRlk4DMYGbZ6ehm2z8enJSVE7uM8MBQIIbeJpbEz/jfxHUOnR0UDAAB5l07JT4/8N4bRUlpYaiDyhlU1NQERPh8Mr7csBEj7fth9vbjZE6l++NRUKwfwfOaHqauA19o2OrrTLZfAd7okJdH6X8YnHk/B9z9RQHVAoLERzXjDP9vcjekCe37FjBY3/pq8OD6N2i+nz+8VokNmq69cTN+8ZoyINAjduwLrbcd/u3cCI1pw6tQLGX+RoXx/YLfG3GhuXGr/rNxXLwpgVgcFi86uTJ0G/lL1tbdnzLU3a290NT0fi/vZ2ERFQvV91yg9ZkkBbpDQazZ7xBOxr4o5EUhl3HGBRlA805vVm3b/K13rNuU7KAGoPKOTmgo5JT3V11gMg7fJxTU1SN80ASO5QXp7bDUT5Ij8/+/71DP2rVyM5H+aVbNmSqjxlQrAto6K2FviTXxxZOE6BbQOiZ7u7F11X8bW1AW8wYzhYurrNWOPxLJyHQverckAk7/CeozXZyZsjI2AHjDVer8j2QLx2//7EEXjY3lhVRWKup8YmuurqHLXsgX3QYKe1dXx8mT1uj/lRNKoa9FkTra3JcZuKb+HvN/ncXZ6l/J3W1vHx9APY3++yGk+cUA36rGdv3lQNfmvGurpUBwdh7dr0G7TAk+S9o3NHN+0ASzuXMcIH6jjfGP0NXD2f3hocCJUAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjMtMDItMTNUMDY6MDU6MTcrMDA6MDCg/5h7AAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIzLTAyLTEzVDA2OjA1OjE3KzAwOjAw0aIgxwAAACh0RVh0ZGF0ZTp0aW1lc3RhbXAAMjAyMy0wMi0xM1QwNjowNToxNyswMDowMIa3ARgAAAAASUVORK5CYII="/>
                    </defs>
                </svg>
							 </div>
							</div>';
            }
        } else {
            return '';
        }
    }
}
