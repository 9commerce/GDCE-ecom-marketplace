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
namespace Ced\CsMarketplace\Controller\Customer;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Customer\Helper\View as CustomerHelperView;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\Address\Mapper as AddressMapper;

class Orders extends \Ced\CsMarketplace\Controller\Customer\Edit
{
    /**
     * Country constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection
     */
    public function __construct(
        Context $context,
        UrlFactory $urlFactory,
        Registry $coreRegistry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        AddressMapper $addressMapper,
        BackendSession $backendSession,
        CustomerHelperView $viewHelper,
        LayoutFactory $resultLayoutFactory

    ) {
        parent::__construct($context,$urlFactory,$coreRegistry,$jsonFactory,$csmarketplaceHelper,$aclHelper,$vendor,$customerSession,$resultPageFactory, $customerRepository,$dataObjectHelper, $customerMapper,$addressMapper,$backendSession,$viewHelper);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Customer orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
