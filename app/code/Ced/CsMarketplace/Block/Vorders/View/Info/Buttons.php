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

namespace Ced\CsMarketplace\Block\Vorders\View\Info;


use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Buttons
 * @package Ced\CsMarketplace\Block\Vorders\View\Info
 */
class Buttons extends Template
{

    /**
     * @var Registry|null
     */
    public $_coreRegistry = null;

    /**
     * Buttons constructor.
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
    }
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('vorders/view/info/buttons.phtml');
    }

    /**
     * Retrieve current order model instance
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve current vendor order model instance
     * @return mixed
     */
    public function getVOrder()
    {
        return $this->_coreRegistry->registry('current_vorder');
    }

    /**
     * Get url for printing order
     *
     * @param \Ced\CsMarketplace\Model\Vorders $vorder
     * @return string
     */
    public function getPrintUrl($vorder)
    {
        return $this->getUrl(
            'csmarketplace/vorders/print',
            ['order_id' => $vorder->getId(), '_secure' => true, '_nosid' => true]
        );
    }
}
