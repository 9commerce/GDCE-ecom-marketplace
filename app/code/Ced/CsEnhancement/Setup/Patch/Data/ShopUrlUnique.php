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
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Ced\CsMarketplace\Setup\CsMarketplaceSetupFactory;

class ShopUrlUnique implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface 
     */
    private $moduleDataSetup;

    /**
     * @var CsMarketplaceSetupFactory 
     */
    private $csMarketplaceSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CsMarketplaceSetupFactory $csMarketplaceSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CsMarketplaceSetupFactory $csMarketplaceSetupFactory
    ){
        $this->moduleDataSetup = $moduleDataSetup;
        $this->csMarketplaceSetupFactory = $csMarketplaceSetupFactory;
    }

    /**
     * @return ShopUrlUnique|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $csMarketplaceSetup = $this->csMarketplaceSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $csMarketplaceSetup->updateAttribute(
            'csmarketplace_vendor',
            'shop_url',
            [
                'is_unique' => true,
            ]
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
