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

namespace Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer;


use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

/**
 * Class Vendorname
 * @package Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer
 */
class Vendorname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrder;

    /**
     * Vendorname constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param Order $salesOrder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        Order $salesOrder,
        array $data = []
    ) {
        $this->design = $design;
        $this->vendorFactory = $vendorFactory;
        $this->salesOrder = $salesOrder;
        parent::__construct($context, $data);
    }

    /**
     * Return the Vendor Link
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $area = $this->design->getArea();
        if ($row->getVendorId() != '') {
            $vendor = $this->vendorFactory->create()->load($row->getVendorId());
            if ($vendor->getName() != NULL) {
                $url = 'javascript:void(0);';
                $target = "";
                if ($area == 'adminhtml') {
                    $url = $this->getUrl("csmarketplace/vendor/edit/", array('vendor_id' => $vendor->getId()));
                    $target = "target='_blank'";
                }
                return "<a href='" . $url . "' " . $target . " >" . $vendor->getName() . "</a>";
            } else {
                $array_data = json_decode($row['amount_desc'], true);
                if(null != $array_data ){
                    $firstKey = array_key_first($array_data);
                    $orderInfo = $this->salesOrder->loadByIncrementId($firstKey);
                    foreach ($orderInfo->getAllItems() as $item1) {
                        $options = $item1->getProductOptions();
                        if (!empty($options['info_buyRequest'])) {
                            if (!empty($options['additional_options'][0]['value'])) {
                                $vendor_quote_name = $options['additional_options'][0]['value'];
                                return $vendor_quote_name;
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
}
