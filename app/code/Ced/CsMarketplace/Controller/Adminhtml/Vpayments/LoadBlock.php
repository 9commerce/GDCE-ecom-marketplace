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

namespace Ced\CsMarketplace\Controller\Adminhtml\Vpayments;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class LoadBlock
 * @package Ced\CsMarketplace\Controller\Adminhtml\Vpayments
 */
class LoadBlock extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Layout\Builder
     */
    protected $_builder;

    /**
     * LoadBlock constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Layout\Builder $builder
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\View\Layout\Builder $builder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_builder = $builder;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->resultPageFactory->create(true)->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('sales_order_create_load_block_json');
        } else {
            $update->addHandle('sales_order_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }
            foreach ($blocks as $block) {
                $update->addHandle('sales_order_create_load_block_' . $block);
            }
        }

        $result = $this->resultPageFactory->create(true)->getLayout()
            ->createBlock(
                \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder\Search\Grid::class
            )->toHtml();
        if ($request->getParam('as_js_varname')) {
            $this->messageManager->setUpdateResult($result);
            return $this->_redirect('*/*/showUpdateResult');
        } else {
            return $this->getResponse()->setBody($result);
        }
    }
}