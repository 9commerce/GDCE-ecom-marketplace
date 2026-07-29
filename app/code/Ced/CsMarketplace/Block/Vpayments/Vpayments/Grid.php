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

namespace Ced\CsMarketplace\Block\Vpayments\Vpayments;

use Ced\CsMarketplace\Block\Vpayments\ListBlock;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\Session;

/**
 * Class Grid
 * @package Ced\CsMarketplace\Block\Vpayments\Vpayments
 */
class Grid extends Extended
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ListBlock
     */
    protected $listBlock;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Session $session
     * @param ListBlock $listBlock
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Session $session,
        ListBlock $listBlock,
        array $data = []
    ) {
        $this->session = $session;
        $this->listBlock = $listBlock;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return mixed
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->session->getVendorId();
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('marketplacetransactionGrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $payments = $this->listBlock->getVpayments();
        $this->setCollection($payments);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'order_date',
            [
                'header' => __('Created At #'),
                'index' => 'created_at',
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'payment_method',
            [
                'header' => __('Payment Mode'),
                'index' => 'payment_method',
                'type' => 'options',
                'options' => array(__('Offline'), __('Online'))
            ]
        );

        $this->addColumn(
            'transaction_id',
            [
                'header' => __('Transaction Id'),
                'index' => 'transaction_id'
            ]
        );

        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'index' => 'amount',
                'type' => 'price'
            ]
        );

        $this->addColumn(
            'fee',
            [
                'header' => __('Adjustment Amount'),
                'index' => 'fee',
                'type' => 'price'
            ]
        );

        $this->addColumn(
            'net_amount',
            [
                'header' => __('Net Amount'),
                'index' => 'net_amount',
                'type' => 'price'
            ]
        );

        $this->addColumn(
            'edits',
            [
                'header' => __('Action'),
                'caption' => __('Action'),
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Ced\CsMarketplace\Block\Vpayments\Vpayments\Renderer\Action',
            ]
        );


        return parent::_prepareColumns();
    }
}
