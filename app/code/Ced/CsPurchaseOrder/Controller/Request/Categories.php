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

namespace Ced\CsPurchaseOrder\Controller\Request;

use Magento\Framework\App\Action\Context;

class Categories extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categorycollection,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Customer\Model\Session $session
    )
    {
        parent::__construct($context);
        $this->urlModel = $urlFactory;
        $this->_customerSession = $session;
        $this->_resultPageFactory = $resultPageFactory;
        $this->category=$category;
        $this->categoryCollection=$categorycollection;
        $this->resultJsonFactory=$jsonFactory;
    }

    public function execute()
    {
        $pathName=[];
        $leavepathId=[];
        $catId=$this->getRequest()->getParam('id');
        $this->category->load($catId);
        $pathIds=explode('/',$this->category->getPath());
        unset($pathIds[0]);
        unset($pathIds[1]);
        foreach ($pathIds as $pathId){
            $catModel=$this->categoryCollection->create()->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id',$pathId);
            foreach ($catModel as $cat) {
                $pathName[] = $cat->getName();
                $leavepathId[] = $cat->getId();
            }
        }
        $leave=implode('->',$pathName);
        $resultPage = $this->_resultPageFactory->create();
        $html = $resultPage->getLayout()
            ->createBlock('Ced\CsPurchaseOrder\Block\Requestform')
            ->setTemplate('Ced_CsPurchaseOrder::newform.phtml')
            ->toHtml();
        $resultJson = $this->resultJsonFactory->create();
        $data=[
            'html' => "<div class='rfqwrap'>".$html."</div>",
            'category_id' => $leave,
            'id' => end($leavepathId)
        ];

        return $resultJson->setData($data);

    }
}