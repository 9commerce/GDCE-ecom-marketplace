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

namespace Ced\CsMarketplace\Model\System\Config;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class TransactionType
 * @package Ced\CsMarketplace\Model\System\Config
 */
class TransactionType implements ArrayInterface
{

    const TRANSACTION_TYPE_CREDIT = 0;

   // const TRANSACTION_TYPE_DEBIT = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => self::TRANSACTION_TYPE_CREDIT, 'label' => __('Credit')]
          //  ,['value' => self::TRANSACTION_TYPE_DEBIT, 'label' => __('Debit')]
        ];
        return $options;
    }

}