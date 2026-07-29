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
 * @package   Ced_VendorsocialLogin
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\VendorsocialLogin\ViewModel;

/**
 * Class DirRegion
 * @package Ced\VendorsocialLogin\ViewModel
 */
class DirRegion implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $helperData;
    /**
     * Google constructor.
     * @param \\Magento\Directory\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Directory\Helper\Data $helperData
    ) {
        $this->helperData= $helperData;
    }
    public function getDirRegion()
    {
        return $this->helperData>getRegionJson();
    }
    public function getCountriesWithOptionalZip()
    {
        return $this->helperData>getCountriesWithOptionalZip(true);
    }
    
}
