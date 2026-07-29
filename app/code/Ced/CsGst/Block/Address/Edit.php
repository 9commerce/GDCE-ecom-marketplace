<?php

namespace Ced\CsGst\Block\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Edit extends Template
{
    private $address;

    private $addressRepository;

    private $addressFactory;

    private $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressRepository = $addressRepository;
        $this->addressFactory  = $addressFactory;
        $this->customerSession = $customerSession;
    }

    protected function _prepareLayout()
    {
        $addressId = $this->getRequest()->getParam('id');
        if ($addressId) {
            try {
                $this->address = $this->addressRepository->getById($addressId);
                if ($this->address->getCustomerId() != $this->customerSession->getCustomerId()) {
                    $this->address = null;
                }
            } catch (NoSuchEntityException $exception) {
                $this->address = null;
            }
        }

        if ($this->address === null) {
            $this->address = $this->addressFactory->create();
        }

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $customBlock  = $this->getLayout()->createBlock(
            '\Ced\CsGst\Block\Customer\Widget\AddressType'
        );
        $customBlock->setAddress($this->address);
        return $customBlock->toHtml();
    }
}