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
 * @category  Ced
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsProAssign\Block\Adminhtml\Vendor\Products\Renderer;

/**
 * Class Remove
 * @package Ced\CsMarketplace\Block\Adminhtml\Vendor\Products\Renderer
 */
class Remove extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $id = $row->getId();
        $html = '<a href="'.$this->getUrl('csassign/assign/remove',['id'=>$id,'vendor_id'=>$this->getRequest()->getParam('vendor_id')]).'">'.__("Remove").'</a>';
        return $html;
    }
}
