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
 * Class VendorStatus
 * @package Ced\CsPurchaseOrder\Model\Quote\Source
 */
class VendorStatus implements OptionSourceInterface
{

    /**
     * when quote is created
     */
    const NEW = 'new';

    /**
     * when customer updated the quote
     */
    const UPDATED_BY_CUSTOMER = 'updated_by_customer';

    /**
     *when vendor updated the quote
     */
    const UPDATED_BY_VENDOR = 'updated_by_vendor';

    /**
     *when customer rejected the quote
     */
    const REJECTED_BY_CUSTOMER = 'rejected_by_customer';

    /**
     *when vendor rejected the quote
     */
    const REJECTED_BY_VENDOR = 'rejected_by_vendor';

    /**
     * when customer approves the quote
     */
    const APPROVED_BY_CUSTOMER = 'approved_by_customer';

    /**
     * when vendor approves the quote
     */
    const APPROVED_BY_VENDOR = 'approved_by_vendor';

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
                    'value' => self::NEW,
                    'label' => __('New')
                ],
                [
                    'value' => self::UPDATED_BY_CUSTOMER,
                    'label' => __('Updated By Customer')
                ],
                [
                    'value' => self::UPDATED_BY_VENDOR,
                    'label' => __('Updated By Vendor')
                ],
                [
                    'value' => self::REJECTED_BY_CUSTOMER,
                    'label' => __('Rejected By Customer')
                ],
                [
                    'value' => self::REJECTED_BY_VENDOR,
                    'label' => __('Rejected By Vendor')
                ],
                [
                    'value' => self::APPROVED_BY_CUSTOMER,
                    'label' => __('Approved By Customer')
                ],
                [
                    'value' => self::APPROVED_BY_VENDOR,
                    'label' => __('Approved By Vendor')
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
