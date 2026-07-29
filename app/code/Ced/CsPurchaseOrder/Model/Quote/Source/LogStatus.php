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

namespace Ced\CsPurchaseOrder\Model\Quote\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LogStatus
 * @package Ced\CsPurchaseOrder\Model\Quote\Source
 */
class LogStatus implements OptionSourceInterface
{

    /**
     *when new quote is created
     */
    const CREATED = 'created';

    /**
     *when quote is update by customer and admin
     */
    const UPDATED = 'updated';

    /**
     *when the quote is closed
     */
    const CLOSED = 'closed';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::CREATED,
                    'label' => __('Created')
                ],
                [
                    'value' => self::UPDATED,
                    'label' => __('Updated')
                ],
                [
                    'value' => self::CLOSED,
                    'label' => __('Closed')
                ]
            ];
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
