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

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $cspurchaseOrdercollectionFactory;

    protected $vendorStatuscollectionFactory;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder\CollectionFactory $cspurchaseOrdercollectionFactory,
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatuscollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->cspurchaseOrdercollectionFactory = $cspurchaseOrdercollectionFactory;
        $this->vendorStatuscollectionFactory = $vendorStatuscollectionFactory;

    }

    /**
     * @return bool|\Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder\Collection
     */
    public function getRequestCollection()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
        $cspurchaseOrdercollection = $this->cspurchaseOrdercollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId());
        $cspurchaseOrdercollection->setPageSize($pageSize);
        $cspurchaseOrdercollection->setCurPage($page);
        return $cspurchaseOrdercollection->setOrder('id','DESC');

    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRequestCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'cspurchaseorder.history.pager'
            )
                ->setAvailableLimit(array(5 => 5, 10 => 10, 15 => 15, 20 => 20))->setShowPerPage(true)->setCollection(
                    $this->getRequestCollection()
                )
                ->setCollection(
                    $this->getRequestCollection()
                );

            $this->setChild('pager', $pager);
            $this->getRequestCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getApprovedVendorId($requestId){
        $vendorId = $this->vendorStatuscollectionFactory->create()
            ->addFieldToFilter('c_quote_id',$requestId)
            ->addFieldToFilter('who_is',1)
            ->addFieldToFilter('is_approved',1)->getLastItem()->getVendorId();

        return $vendorId;
    }

}
