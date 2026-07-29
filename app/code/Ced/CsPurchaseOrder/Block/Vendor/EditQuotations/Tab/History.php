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

namespace Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab;

/**
 * Class History
 * @package Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab
 */
class History extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory
     */
    protected $commentsCollectionFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * History constructor.
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->commentsCollectionFactory = $commentsCollectionFactory;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @return \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\Collection
     */
    public function getCommentHistory()
    {
        $commentHistoy = $this->commentsCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('vendor_id', $this->sessionFactory->create()->getVendorId());

        return $commentHistoy;
    }

}
