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

namespace Ced\CsVendorReview\Block;

use Ced\CsVendorReview\Helper\Data;
use Magento\Framework\UrlFactory;
use Ced\CsVendorReview\Block\Context;
use Magento\Framework\View\Element\Template;

class BaseBlock extends Template
{
    /**
     * @var Data
     */
    protected $_devToolHelper;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_urlApp;

    /**
     * @var \Ced\CsVendorReview\Model\Config
     */
    protected $_config;

    /**
     * @var Data
     */
    public $reviewHelper;

    /**
     * @param \Ced\CsVendorReview\Block\Context $context
     * @param Data $reviewHelper
     */
    public function __construct(
        Context $context,
        Data $reviewHelper
    ) {
        $this->_devToolHelper = $context->getCsVendorReviewHelper();
        $this->_config = $context->getConfig();
        $this->_urlApp = $context->getUrlFactory()->create();
        $this->reviewHelper = $reviewHelper;
        parent::__construct($context);
    }

    /**
     * Function for getting event details
     *
     * @return array
     */
    public function getEventDetails()
    {
        return  $this->_devToolHelper->getEventDetails();
    }

    /**
     * Function for getting current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlApp->getCurrentUrl();
    }

    /**
     * Function for getting controller url for given router path
     *
     * @param  string $routePath
     * @return string
     */
    public function getControllerUrl($routePath)
    {
        return $this->_urlApp->getUrl($routePath);
    }

    /**
     * Function for getting current url
     *
     * @param  string $path
     * @return string
     */
    public function getConfigValue($path)
    {
        return $this->_config->getCurrentStoreConfigValue($path);
    }

    /**
     * Function canShowCsVendorReview
     *
     * @return bool
     */
    public function canShowCsVendorReview()
    {
        $isEnabled=$this->getConfigValue('csvendorreview/module/is_enabled');
        if ($isEnabled) {
            $allowedIps = $this->getConfigValue('csvendorreview/module/allowed_ip');
            if (!$allowedIps) {
                return true;
            } else {
                $remoteIp = $this->reviewHelper->getRemoteAddress();
                if (strpos($allowedIps, $remoteIp) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
