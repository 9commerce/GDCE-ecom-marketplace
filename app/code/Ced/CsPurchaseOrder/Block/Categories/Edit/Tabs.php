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

namespace Ced\CsPurchaseOrder\Block\Categories\Edit;

/**
 * Class Tabs
 * @package Ced\CsPurchaseOrder\Block\Categories\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Tabs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    )
    {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('form_records');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Categories List'));
        $this->setData('area', 'adminhtml');

    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'assigned_categories_list',
            [
                'label' => __('Categories'),
                'title' => __('Categories'),
                'content' => $this->getLayout()->createBlock('Ced\CsPurchaseOrder\Block\Categories\Assigned')
                    ->setTemplate('Ced_CsPurchaseOrder::categories/assigned.phtml')
                    ->toHtml(),
            ]
        );

        return parent::_beforeToHtml();
    }
}