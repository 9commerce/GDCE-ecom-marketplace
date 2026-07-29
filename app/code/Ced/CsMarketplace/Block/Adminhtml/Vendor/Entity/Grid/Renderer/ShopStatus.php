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


/**
 * Class ShopStatus
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer
 */
class ShopStatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Ced\CsMarketplace\Model\Vshop
     */
    protected $vshop;

    /**
     * ShopStatus constructor.
     * @param \Ced\CsMarketplace\Model\Vshop $vshop
     */
    public function __construct(
        \Ced\CsMarketplace\Model\Vshop $vshop
    ) {
        $this->vshop = $vshop;
    }

    /**
     * shows shop status
     * @param \Magento\Framework\DataObject $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $model = $this->vshop->loadByField('vendor_id', [$row->getEntityId()]);
        if ($model->getId() != '' && $model->getShopDisable() == \Ced\CsMarketplace\Model\Vshop::ENABLED) {
            $html .= __('Enabled');
        } else if ($model->getId() != '' && $model->getShopDisable() == \Ced\CsMarketplace\Model\Vshop::DISABLED) {
            $html .= __('Disabled');
        } else {
            $html .= __('Enabled');
        }
        return $html;
    }
}

