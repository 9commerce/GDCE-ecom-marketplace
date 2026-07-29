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
 * @category  Ced
 * @package   Ced_CsMarketplace
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Message;

use Ced\CsMarketplace\Model\Vproducts;

/**
 * Class PendingProducts
 * @package Ced\CsMarketplace\Model\System\Message
 */
class PendingProducts implements \Magento\Framework\Notification\MessageInterface
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * PendingProducts constructor.
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->vProductsFactory = $vProductsFactory;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        // Retrieve unique message identity
        return sha1('PENDING_PRODUCTS');
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        // Return true to show your message, false to hide it
        return (count($this->vProductsFactory->create()->getVendorProducts(Vproducts::PENDING_STATUS))) ? true : false;
    }

    /**
     * @return string
     */
    public function getText()
    {
        // Retrieve message text
        $productsCount = count($this->vProductsFactory->create()->getVendorProducts(Vproducts::PENDING_STATUS));
        $url = '<a href="'.$this->_urlBuilder->getUrl('csmarketplace/vproducts/pending/check_status/2').'">'.__(' Vendor Pending Products ').'</a>';
        return __('%1 Approval Request for Vendor Product(s). Approve Vendor Product(s) from'.$url,$productsCount);
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        // Possible values: SEVERITY_CRITICAL, SEVERITY_MAJOR, SEVERITY_MINOR, SEVERITY_NOTICE
        return self::SEVERITY_MAJOR;
    }
}
