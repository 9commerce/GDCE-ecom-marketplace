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
 * @package   Ced_VendorsocialLogin
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\VendorsocialLogin\Block\Login;

/**
 * Class Script
 * @package Ced\VendorsocialLogin\Block\Login\Script
 */
class Script extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isSocialLogin(){
        if ($this->_customerSession->getSocialLogin()) {
            $this->_customerSession->unsSocialLogin();
            return '<script>
                         require([
                           "Magento_Customer/js/customer-data"
                        ], function (customerData) {
                           "use strict";
                           var sections = ["customer", "cart"];
                           customerData.initStorage();
                           customerData.invalidate(sections);
                           customerData.reload(sections, true);
                        });
                        </script>';
        }
        return "";
    }
}
