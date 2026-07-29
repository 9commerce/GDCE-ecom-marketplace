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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class VPaymentStatus implements ArrayInterface
{
    /**
     * @var \Ced\CsMarketplace\Model\Vpayment\Requested
     */
    protected $_vpaymentRequested;

    /**
     * VPaymentStatus constructor.
     * @param \Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested
     */
    public function __construct(\Ced\CsMarketplace\Model\Vpayment\Requested $vpaymentRequested)
    {
        $this->_vpaymentRequested = $vpaymentRequested;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_vpaymentRequested->getStatuses() as $id => $state) {
            $options[] = ['value' => $id, 'label' => __($state->render())];
        }
        return $options;
    }
}
