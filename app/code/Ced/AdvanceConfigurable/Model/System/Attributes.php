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
 * @package     Ced_AdvanceConfigurable
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\AdvanceConfigurable\Model\System;

use Magento\Framework\App\Helper\Context;

/**
 * Class Attributes
 * @package Ced\AdvanceConfigurable\Model\System
 */
class Attributes extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Attributes constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
    )
    {
        parent::__construct($context);
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Retrieve Option values array
     *
     * @param boolean $defaultValues
     * @param boolean $withEmpty
     * @return array
     */
    public function toOptionArray($defaultValues = false, $withEmpty = false, $storeId = null)
    {
        $filter_a = array('null' => true);
        $filter_b = array('like' => '%configurable%');
        $attributes = array();
        $attributes = $this->attributeCollectionFactory->create()
            ->load()
            ->addFieldToFilter('is_global', 1)
            ->addFieldToFilter('frontend_input', 'select')
            ->addFieldToFilter('apply_to', array($filter_a, $filter_b))
            ->addFieldToFilter('frontend_input_renderer', array('null' => true))
            ->addFieldToFilter('attribute_code', array('neq' => 'show_matrix'))
            ->getData();

        $options = array();

        foreach ($attributes as $key => $value) {
            $options[] = array('value' => $value['attribute_id'], 'label' => $value['frontend_label']);
        }
        return $options;
    }

}
