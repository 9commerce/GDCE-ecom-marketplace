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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Model\Source\Vendor\Rate;

class Calcmethod extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $label = '';
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                $label = $v['label'];
            }
        }
        return $label;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_FIXED,
                'label' => __('Fixed')
            ],
            [
                'value' => \Ced\CsCommission\Block\Adminhtml\Vendor\Rate\Method::METHOD_PERCENTAGE,
                'label' => __('Percentage')
            ]
        ];
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
