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
declare(strict_types=1);

namespace Ced\VendorsocialLogin\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class CreateCustomerAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return CreateCustomerAttributes|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_gid',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Google id',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_gtoken',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Google token',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_fid',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Facebook id',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_ftoken',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Facebook token',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_tid',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Twitter id',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_ttoken',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Twitter token',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_lid',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Linkedin id',

                'system' => false

            ]
        );

        $eavSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'ced_sociallogin_ltoken',
            [

                'type' => 'text',

                'visible' => false,

                'required' => false,

                'user_defined' => false,

                'label' => 'ced Linkedin token',

                'system' => false

            ]
        );
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
