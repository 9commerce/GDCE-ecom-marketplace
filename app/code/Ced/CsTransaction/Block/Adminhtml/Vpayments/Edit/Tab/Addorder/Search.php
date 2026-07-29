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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab\Addorder;

class Search extends \Magento\Sales\Block\Adminhtml\Order\Create\Search
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
                'label' => __('Add Selected Amount(s) for Payment'),
                'onclick' => 'addorder()',
                'class' => 'add',
        ];
        return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData($addButtonData)->toHtml();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please Select Amount(s) to Add');
    }
}
