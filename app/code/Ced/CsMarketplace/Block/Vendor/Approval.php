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

namespace Ced\CsMarketplace\Block\Vendor;


use Ced\CsMarketplace\Model\Session;
use Ced\CsMarketplace\Model\Url;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Ced\CsMarketplace\Block\Vendor\AbstractBlock;

/**
 * Class Approval
 * @package Ced\CsMarketplace\Block\Vendor
 */
class Approval extends AbstractBlock
{

    /**
     * @var Url
     */
    public $_vendorUrl;

    /**
     * @var UrlFactory
     */
    protected $urlModel;

    /**
     * Approval constructor.
     * @param Url $vendorUrl
     * @param VendorFactory $vendorFactory
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Url $vendorUrl,
        VendorFactory $vendorFactory,
        CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory
    ) {
        $this->_vendorUrl = $vendorUrl;
        $this->urlModel = $urlFactory;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_vendorUrl->getBaseUrl();
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->_vendorUrl->getLogoutUrl();
    }

    /**
     * Approval message
     *
     * @return String
     */
    public function getApprovalMessage()
    {
        $message = __('Please fill this form to create your vendor account.');
        if ($this->getVendorId()) {
            switch ($this->getVendor()->getStatus()) {
                case Vendor::VENDOR_DISAPPROVED_STATUS :
                    $message = __('Your vendor account has been Disapproved.');
                    break;
                default :
                    $message = __('You will receive an email once your account is reviewed.');
                    break;
            }
        }

        return $message;
    }
}
