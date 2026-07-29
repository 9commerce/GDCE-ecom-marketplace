<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ced\CsPurchaseOrder\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class Toc implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    protected $blockFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ){
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /*START : CMS block for TOC sections*/
        $featuresBlock = $this->blockFactory->create();
        $featuresBlock->load('ced-category-customer-toc', 'identifier');
        if (!$featuresBlock->getId()) {
            $features = [
                'title' => 'Customer TOC for Category',
                'identifier' => 'ced-category-customer-toc',
                'content' => '<p style="text-align: center;"> <strong>THIS AGREEMENT WITNESSES AS UNDER</strong></p>
                               <p style="text-align: center;"> Terms and Conditions </p>',
                'stores' => 0,
                'is_active' => 1,
            ];
            $this->blockFactory->create()->setData($features)->save();
        }
        /*END : CMS block for TOC sections*/
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
