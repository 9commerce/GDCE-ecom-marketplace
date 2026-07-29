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
 * @package     Ced_QuickOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\QuickOrder\Observer;

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;

class Quickorder implements ObserverInterface
{

    /**
     * @var Registry
     */
    public $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_customerSession;

    /**
     * Quickorder constructor.
     * @param Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    ){
        $this->_coreRegistry = $registry;
        $this->_customerSession = $customerSession;

    }

    /**
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(null !== $this->_coreRegistry->registry('session')){
            $this->_coreRegistry->unregister('session');
        }

        return $this->_coreRegistry->register('session', $this->_customerSession->isLoggedIn());
    }

}
