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
 * Class AssignedList
 * @package Ced\CsPurchaseOrder\Block\Vendor\EditQuotations\Tab
 */
class AssignedList extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $session;

    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    public $purchaseorderFactory;

    /**
     * AssignedList constructor.
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\Session $session,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->session = $session;
        $this->purchaseorderFactory = $purchaseorderFactory;
    }

    /**
     * @return mixed
     */
    public function getPoDetail()
    {

        return $this->_coreRegistry->registry('porequest');

    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getImageSrc()
    {
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/images/' . $this
                ->_coreRegistry->registry('porequest')->getCustomerId() . '/';
        return $url;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public Function getFileSrc()
    {
        $url = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'cspurchaseorder/files/' . $this
                ->_coreRegistry->registry('porequest')->getCustomerId() . '/';
        return $url;
    }

    /**
     * @return mixed
     */
    public function getQuotaionData()
    {
        $modeldata = $this->purchaseorderFactory->create()->load($this->getRequest()->getParam('id'))->getData();
        return $modeldata;
    }
}
