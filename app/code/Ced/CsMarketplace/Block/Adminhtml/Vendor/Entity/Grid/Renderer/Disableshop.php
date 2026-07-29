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

namespace Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer;


use Ced\CsMarketplace\Model\Vshop;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;

/**
 * Class Disableshop
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer
 */
class Disableshop extends AbstractRenderer
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Vshop
     */
    protected $vshop;

    /**
     * Disableshop constructor.
     * @param UrlInterface $urlBuilder
     * @param Vshop $vshop
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Vshop $vshop
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->vshop = $vshop;
    }

    /**
     * Render approval link in each vendor row
     * @param DataObject $row
     * @return String
     */
    public function render(DataObject $row)
    {
        $html = '';
        $model = $this->vshop->loadByField('vendor_id', array($row->getEntityId()));

        if ($model->getId() != '' && $model->getShopDisable() == Vshop::ENABLED) {
            $url = $this->urlBuilder->getUrl('*/*/massDisable',
                array('vendor_id' => $row->getEntityId(), 'shop_disable' => Vshop::DISABLED,
                    'inline' => 1));
            $html .= __('Enabled') . '&nbsp;' . '<a href="javascript:void(0);" onclick="deleteConfirm(\'' .
                __('Are you sure you want to Disable?') . '\', \'' . $url . '\');" >' . __('Disable') . '</a>';
        } else if ($model->getId() != '' && $model->getShopDisable() == Vshop::DISABLED) {
            $url = $this->urlBuilder->getUrl('*/*/massDisable',
                array('vendor_id' => $row->getEntityId(), 'shop_disable' => Vshop::ENABLED,
                    'inline' => 1));

            $html .= __('Disabled') . '&nbsp;' . '<a href="javascript:void(0);" onclick="deleteConfirm(\'' .
                __('Are you sure you want to Enable?') . '\', \'' . $url . '\');" >' . __('Enable') . "</a>";
        } else {
            $url = $this->urlBuilder->getUrl('*/*/massDisable',
                array('vendor_id' => $row->getEntityId(), 'shop_disable' => Vshop::DISABLED,
                    'inline' => 1));

            $html .= __('Enabled') . '&nbsp;' . '<a href="javascript:void(0);" onclick="deleteConfirm(\'' .
                __('Are you sure you want to Disable?') . '\', \'' . $url . '\');" >' . __('Disable') . '</a>';
        }
        return $html;
    }
}