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

class Aggregrade extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const TYPE_MAX = 'MAX';
    const TYPE_MIN = 'MIN';

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
            ['value' => self::TYPE_MAX, 'label' => __('max()')],
            ['value' => self::TYPE_MIN, 'label' => __('min()')]
        ];
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = [];
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
