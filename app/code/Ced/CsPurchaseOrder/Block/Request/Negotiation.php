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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Block\Request;

use Ced\CsPurchaseOrder\Model\Quote\Source\VendorStatus;
use Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory as VendorStatusCollectionFactory;
use Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory as CommentsCollectionFactory;

/**
 * Class Negotiation
 * @package Ced\CsPurchaseOrder\Block\Request
 */
class Negotiation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var VendorStatusCollectionFactory
     */
    protected $vendorStatusCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $productTypeConfigurable;

    /**
     * @var CommentsCollectionFactory
     */
    protected $commentsCollectionFactory;

    /**
     * Negotiation constructor.
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param VendorStatusCollectionFactory $vendorStatusCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable
     * @param CommentsCollectionFactory $commentsCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\App\Request\Http $httpRequest,
        VendorStatusCollectionFactory $vendorStatusCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable,
        CommentsCollectionFactory $commentsCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->sessionFactory = $sessionFactory;
        $this->httpRequest = $httpRequest;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->productFactory = $productFactory;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->commentsCollectionFactory = $commentsCollectionFactory;
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->sessionFactory->create()->getCustomerId();
    }

    /**
     * @return mixed
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'cspurchaseorder/request/savenegotiation',
            ['_secure' => true]
        );
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->httpRequest->getParam('vendor_id');
    }

    /**
     * @return mixed
     */
    public function getQuoteId()
    {
        return $this->httpRequest->getParam('requestid');
    }

    /**
     * @return mixed
     */
    public function getCommentHistory()
    {
        $commentHistoy = $this->commentsCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getQuoteId())
            ->addFieldToFilter('vendor_id', $this->getVendorId());

        return $commentHistoy;
    }

    /**
     * @return mixed
     */
    public function getNegotiationInfo()
    {
        $status = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getQuoteId())
            ->addFieldToFilter('vendor_id', $this->getVendorId())
            ->getLastItem();
        return $status;
    }

    /**
     * @param $productid
     * @return \Magento\Catalog\Model\Product
     */
    public function isConfigurableProduct($productid)
    {

        $type = $this->productFactory->create()->load($productid)->getTypeId();
        if ($type == 'virtual') {
            $productid = $this->productTypeConfigurable->getParentIdsByChild($productid);
        }
        return $this->productFactory->create()->load($productid);
    }

    /**
     * @return bool
     */
    public function canSubmit()
    {
        $vendor_status = $this->getNegotiationInfo()->getVendorStatus();

        if($vendor_status == VendorStatus::UPDATED_BY_VENDOR || $vendor_status == VendorStatus::UPDATED_BY_CUSTOMER )
            return true;

        return false;
    }

}
