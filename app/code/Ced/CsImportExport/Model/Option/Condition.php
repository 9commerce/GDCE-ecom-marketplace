<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Model\Option;

class Condition
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Genereal Contact')], ['value' => 1, 'label' => __('Sales Represntative')],['value' => 2, 'label' => __('Customer Support')],['value' => 3, 'label' => __('Customer Email1')],['value' => 4, 'label' => __('Customer Email2')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [4 => __('Customer Email2'),3 => __('Customer Email1'),2 => __('Customer Support'), 1 => __('Sales Represntative'),0 => __('Genereal Contact')];
    }
}
