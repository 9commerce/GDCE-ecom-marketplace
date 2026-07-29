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

namespace Ced\CsProduct\Ui\DataProvider\Product\Config;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class StockData extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === ConfigurableType::TYPE_CODE) {
            $config['arguments']['data']['config'] = [
                'visible' => '0',
                'imports' => [
                    'visible' => null,
                ],
            ];

            $meta['advanced_inventory_modal'] = [
                'children' => [
                    'stock_data' => [
                        'children' => [
                            'qty' => $config,
                            'container_min_qty' => $config,
                            'container_min_sale_qty' => $config,
                            'container_max_sale_qty' => $config,
                            'is_qty_decimal' => $config,
                            'is_decimal_divided' => $config,
                            'container_backorders' => $config,
                            'container_notify_stock_qty' => $config,
                        ],
                    ],
                ],
            ];
        } else {
            $config['arguments']['data']['config'] = [
                'imports' => [
                    'disabled' => '!ns = ${ $.ns }, index = '
                        . ConfigurablePanel::CONFIGURABLE_MATRIX . ':isEmpty',
                ],
            ];

            $meta['advanced_inventory_modal'] = [
                'children' => [
                    'stock_data' => [
                        'children' => [
                            'qty' => $config,
                        ],
                    ],
                ],
            ];
        }

        return $meta;
    }
}
