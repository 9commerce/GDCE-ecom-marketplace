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

namespace Ced\CsPurchaseOrder\Block;

/**
 * Class Requestform
 * @package Ced\CsPurchaseOrder\Block
 */
class Requestform extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var
     */
    private $options;

    /**
     * @var \Ced\CsPurchaseOrder\Model\Purchaseorder
     */
    public $purchaseOrder;

    /**
     * @var \Magento\Directory\Block\Data
     */
    public $blockData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory
     */
    protected $commentsCollectionFactory;


    /**
     * Requestform constructor.
     * @param \Ced\CsPurchaseOrder\Model\AttachmentsFactory $attachmentsFactory
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseOrder
     * @param \Magento\Directory\Block\Data $blockData
     * @param \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\AttachmentsFactory $attachmentsFactory,
        \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\CollectionFactory $vendorStatusCollectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseOrder,
        \Magento\Directory\Block\Data $blockData,
        \Ced\CsPurchaseOrder\Model\ResourceModel\Comments\CollectionFactory $commentsCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->attachmentsFactory = $attachmentsFactory;
        $this->_customerSession = $customerSession;
        $this->_categoryFactory = $categoryFactory;
        $this->_request = $request;
        $this->purchaseOrder = $purchaseOrder;
        $this->blockData = $blockData;
        $this->commentsCollectionFactory = $commentsCollectionFactory;
        $this->vendorStatusCollectionFactory = $vendorStatusCollectionFactory;
        $this->vendorFactory = $vendorFactory;
        $this->_coreRegistry = $registry;
        if ($this->_request->getParam('requestid')) {
            $requestcolection = $this->purchaseOrder->create()->load($this->_request->getParam('requestid'));
            $this->setCollection($requestcolection);
        }
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        if ($this->getCollection())
            return $this->getCollection()->getImages();
    }

    /**
     * @return mixed
     */
    public function getOrderid()
    {
        if ($this->getCollection())
            return $this->getCollection()->getOrderid();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getImageSrc()
    {
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/images/' . $this->_customerSession->getCustomerId() . '/';
        return $url;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getFileSrc()
    {
        if ($this->_request->getParam('requestid')) {
            $attachment = $this
                ->attachmentsFactory->create()->load($this->_request->getParam('requestid'), 'c_quote_id');

            return $attachment->getFilePath();
        }
    }

    /**
     * @return mixed
     */
    public Function getFileName()
    {
        if ($this->_request->getParam('requestid')) {
            $attachment = $this
                ->attachmentsFactory->create()->load($this->_request->getParam('requestid'), 'c_quote_id');

            return $attachment->getFileName();
        }
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Read|string
     */
    public function getMediaDirectory()
    {
        $mediaDirectory = $this->_filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath('cspurchaseorder/images/' . $this->_customerSession->getCustomerId());

        return $path;
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'cspurchaseorder/index/save',
            ['_secure' => true,]
        );
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $rootCategoryId = $this->_storeManager->getStore($storeId)->getRootCategoryId();

        $this->options = array();
        $collection = $this->_categoryFactory->create()->getCollection()
            ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'level', 'children'])
            ->addAttributeToFilter('parent_id', $rootCategoryId);
        $categoryById = [
            $rootCategoryId => [
                'id' => $rootCategoryId,
                'children' => [],
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['level'] = $category->getLevel();
            $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
        }
        $this->renederCat($categoryById[$rootCategoryId]['children']);

        return $this->options;
    }

    /**
     * @param $data
     */
    public function renederCat($data)
    {
        foreach ($data as $cat) {
            $this->options[] = array('value' => $cat['id'], 'label' => __($cat['label']));
        }
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

    /**
     * @return \Ced\CsPurchaseOrder\Model\ResourceModel\VendorStatus\Collection
     */
    public function getVendors()
    {
        $vendors = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('requestid'))
            ->addFieldToFilter('vendor_replied', 1);
        return $vendors;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorStatus($vendorId)
    {

        $vendorStatus = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('requestid'))
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setOrder('created_at', 'ASC')
            ->getLastItem()
            ->getVendorStatus();

        return $vendorStatus;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorName($vendorId)
    {
        return $this->vendorFactory->create()->load($vendorId)->getPublicName();
    }

    /**
     * @param $vendorId
     * @return \Magento\Framework\DataObject
     */
    public function getNegotiationInfo($vendorId)
    {
        $vendorCollection = $this->commentsCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('requestid'))
            ->addFieldToFilter('vendor_id', $vendorId)->getLastItem();
        return $vendorCollection;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function isApproved($vendorId)
    {
        $isApproved = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('requestid'))
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('who_is', 1)
            ->getLastItem()
            ->getIsApproved();

        return $isApproved;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getStatusId($vendorId)
    {
        $isApproved = $this->vendorStatusCollectionFactory->create()
            ->addFieldToFilter('c_quote_id', $this->getRequest()->getParam('requestid'))
            ->addFieldToFilter('vendor_id', $vendorId)
            ->getLastItem()
            ->getId();

        return $isApproved;
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        if ($this->_coreRegistry->registry('current_vendor')) {
            return $this->_coreRegistry->registry('current_vendor')->getId();
        }
    }
}
