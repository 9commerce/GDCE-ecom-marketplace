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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Block\Order\View;

class Form extends \Magento\Backend\Block\Template
{
    /**
     * Template
     * @var string
     */
    protected $_template = 'order/view/form.phtml';

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->setData('area', 'adminhtml');
    }
}
