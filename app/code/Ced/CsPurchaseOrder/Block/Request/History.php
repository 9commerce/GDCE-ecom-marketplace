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

namespace Ced\CsPurchaseOrder\Block\Request;

/**
 * Class History
 * @package Ced\CsPurchaseOrder\Block\Request
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory
     */
    protected $commentsCollectionFactory;

    /**
     * History constructor.
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->commentsCollectionFactory = $commentsCollectionFactory;
    }

    /**
     * @return array
     */
    public function getCommentHistory()
    {
        $commentshistory = array();
        if ($this->getRequest()->getParam('requestid')) {
            $commentshistory = $this->commentsCollectionFactory->create()
                ->addFieldToFilter('request_id', $this->getRequest()->getParam('requestid'));

        }
        return $commentshistory;
    }
}
