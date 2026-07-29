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
namespace Ced\CsImportExport\Model\Import\Product\Type;

/**
 * Import entity simple product type
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Simple extends \Ced\CsImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Attributes' codes which will be allowed anyway, independently from its visibility property.
     *
     * @var string[]
     */
    protected $_forcedAttributesCodes = [
        'related_tgtr_position_behavior',
        'related_tgtr_position_limit',
        'upsell_tgtr_position_behavior',
        'upsell_tgtr_position_limit',
        'thumbnail_label',
        'small_image_label',
        'image_label',
    ];
}
