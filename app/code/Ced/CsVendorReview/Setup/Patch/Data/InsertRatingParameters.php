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
 * @package     Ced_CsVendorReview
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
declare(strict_types=1);

namespace Ced\CsVendorReview\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class InsertRatingParameters implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $tableName = $this->moduleDataSetup->getTable('ced_csvendorreview_rating');
        if ($this->moduleDataSetup->getConnection()->isTableExists($tableName) == true) {
            $data = [
                [
                    'rating_label' => 'Quality',
                    'rating_code' => 'quality',
                    'sort_order' => 1,
                ],
                [
                    'rating_label' => 'Pricing',
                    'rating_code' => 'pricing',
                    'sort_order' => 2,
                ],
                [
                    'rating_label' => 'Service',
                    'rating_code' => 'service',
                    'sort_order' => 3,
                ]
            ];
            foreach ($data as $item) {
                $this->moduleDataSetup->getConnection()->insert($tableName, $item);
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
