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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Model;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class ProductNames
 * @package Ced\CsPurchaseOrder\Model
 */
class ProductNames implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogCollectionFactory;

    /**
     * ProductNames constructor.
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollectionFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogCollectionFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
    )
    {
        $this->sessionFactory = $sessionFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->catalogCollectionFactory = $catalogCollectionFactory;
    }

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $productNames = $this->vproductsFactory->create()->getVendorProducts(1,
            $this->sessionFactory->create()->getVendorId(), 0)
            ->addFieldToFilter('type', ['nin' => ['configurable']])
            ->getColumnValues('product_id');

        $product = $this->catalogCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $productNames])
            ->addFieldToFilter('status', Status::STATUS_ENABLED);

        if (!$this->options) {
            $this->options[] = ['value' => '', 'label' => __('Please Select Product')];
            foreach ($product as $name) {
                $this->options[] = [

                    'value' => $name->getId(),
                    'label' => __($name->getSku())

                ];
            }
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $optionItem) {
            $options[$optionItem['value']] = $optionItem['label'];
        }
        return $options;
    }
}
