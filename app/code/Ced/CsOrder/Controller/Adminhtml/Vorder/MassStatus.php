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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Controller\Adminhtml\Vorder;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ced\CsMarketplace\Model\ResourceModel\Vorders\CollectionFactory as VordersCollection;
use Ced\CsMarketplace\Model\ResourceModel\Vorders as VordersResource;
use Ced\CsMarketplace\Model\VordersFactory as VordersModel;

/**
 * Updates status of vendor order approval.
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var VordersCollection
     */
    protected $collectionFactory;
    /**
     * @var VordersModel
     */
    protected $vordersModel;
    /**
     * @var VordersResource
     */
    protected $vordersResource;
    /**
     * @param Action\Context $context
     * @param Filter $filter
     * @param VordersCollection $collectionFactory
     * @param VordersModel $vordersModel
     * @param VordersResource $vordersResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        VordersCollection $collectionFactory,
        VordersModel $vordersModel,
        VordersResource $vordersResource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->vordersModel = $vordersModel;
        $this->vordersResource = $vordersResource;
    }

    /**
     * Method changing the status of selected ids
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        $vorderIds = $collection->getColumnValues('id');
        $changestatus  = (int) $this->getRequest()->getParam('status');

        if (!is_array($vorderIds)) {
            $this->messageManager->addErrorMessage(__('Please select Order(s).'));
        } elseif (!empty($vorderIds) && $changestatus !=='') {
            try {
                $model = $this->vordersModel->create();
                foreach ($collection as $item) {
                    $this->vordersResource->load($model, $item->getId());
                    if ($model->getVendorOrderApproval() ==
                        \Ced\CsOrder\Model\System\Config\VendorOrderApproval::STATE_DISAPPROVED
                    && $changestatus ==  \Ced\CsOrder\Model\System\Config\VendorOrderApproval::STATE_APPROVED) {
                        $this->messageManager->addErrorMessage(
                            __('Disapproved status cannot be changed for order %1', $model->getOrderId())
                        );
                        $collectionSize--;
                    } else {
                        $model->setVendorOrderApproval($changestatus);
                        $this->vordersResource->save($model);
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 order(s) approval status changed.', $collectionSize)
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('%1', $e->getMessage()));
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->_redirect->getRefererUrl();
        return $resultRedirect->setPath($url);
    }
}
