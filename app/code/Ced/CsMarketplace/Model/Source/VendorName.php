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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\Source;

/**
 * Class VendorName
 * @package Ced\CsMarketplace\Model\Source
 */
class VendorName implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $_vendorCollectionFactory;

    /**
     * @var array
     */
    public $_options = [];

    /**
     * VendorName constructor.
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     */
    function __construct(
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
    ) {
        $this->_vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $allVendors = [];
        if(!$this->_options){
            $vendorCollection = $this->_vendorCollectionFactory->create()
                ->addAttributeToSelect(['public_name']);
            foreach($vendorCollection as $vendor){
                $allVendors[] = [
                    'value' => $vendor->getId(),
                    'label' => $vendor->getPublicName()
                ];
            }
        }
        $this->_options = $allVendors;
        return $this->_options;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * @return array
     */
    public function getOptionArray(){
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    /**
     * @param $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId){
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }
}
