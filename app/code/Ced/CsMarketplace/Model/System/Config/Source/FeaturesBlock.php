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
 * @package     Ced_CsMarketplace
 * @author           CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source;

/**
 * Class FeaturesBlock
 * @package Ced\CsMarketplace\Model\System\Config\Source
 */
class FeaturesBlock extends \Ced\CsMarketplace\Model\System\Config\Source\AbstractBlock
{

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $blockCollectionFactory;

    /**
     * FeaturesBlock constructor.
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
    )
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve Option values array
     *
     * @param bool $defaultValues
     * @return array
     */
    public function toOptionArray($defaultValues = false)
    {
        $options = [];
        $blockCollection = $this->blockCollectionFactory->create();
        if (!empty($blockCollection)) {
            foreach ($blockCollection as $block) {
                $options[$block->getIdentifier()] = $block->getTitle();

            }
        }
        return $options;
    }
}
