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
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsVendorProductAttribute\Model\System\Config\Source;

/**
 * Class Inputtype
 * @package Ced\CsVendorProductAttribute\Model\System\Config\Source
 */
class Inputtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $optionsArray;

    /**
     * Inputtype constructor.
     * @param array $optionsArray
     */
    public function __construct(array $optionsArray = [])
    {
        $this->optionsArray = $optionsArray;
    }

    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        //sort array elements using key value
        ksort($this->optionsArray);
        return $this->optionsArray;
    }

    /**
     * Get volatile input types.
     *
     * @return array
     */
    public function getVolatileInputTypes()
    {
        return [
            ['textarea']
        ];
    }
}
