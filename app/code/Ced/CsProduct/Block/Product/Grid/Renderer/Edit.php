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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Block\Product\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ProductFactory;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;

class Edit extends AbstractRenderer
{

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Edit constructor.
     * @param ProductFactory $productFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        Context $context,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $product = $this->productFactory->create()->load($row->getProductId());
        $attributeSetId = $product->getAttributeSetId();
        $url = $this->getUrl('*/*/edit', ['set' => $attributeSetId, 'id' => $row->getProductId(),
            'store' => (int)$this->getRequest()->getParam('store', 0), 'type' => $product->getTypeId()]);
        return "<a href='$url' target='_self'>" . __('Edit') . "</a>";
    }
}
