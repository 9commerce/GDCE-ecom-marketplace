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

namespace Ced\CsMarketplace\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;
use Ced\CsMarketplace\Model\Vproducts;

/**
 * Class ProductStatus
 */
class ProductStatus implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'label' => __('Approved'),
            'value' => Vproducts::APPROVED_STATUS,
        ];

        $options[] = [
            'label' => __('Pending'),
            'value' => Vproducts::PENDING_STATUS,
        ];
        $options[] = [
            'label' => __('Disapproved'),
            'value' => Vproducts::NOT_APPROVED_STATUS,
        ];
                     
        return $options;
    }
}
