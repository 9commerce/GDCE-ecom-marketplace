<?php

namespace Ced\CsGst\Block\Customer\Widget;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Api\AddressMetadataInterface;
class AddressType extends Template
{
    const ATTRIBUTE = 'gstin_number';

    private $addressMetadata;

    public function __construct(
        Template\Context $context,
        AddressMetadataInterface $addressMetadata,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addressMetadata = $addressMetadata;
    }

    protected function _construct()
    {
        $this->setTemplate('widget/address.phtml');
        return parent::_construct();
    }

    public function getValue()
    {
        $address = $this->getAddress();
        if ($address instanceof AddressInterface) {
            return $address->getCustomAttribute(self::ATTRIBUTE)
                ? $address->getCustomAttribute(self::ATTRIBUTE)->getValue()
                : null;
        }
    }

    private function getAttribute()
    {
        try {
            $attribute = $this->addressMetadata->getAttributeMetadata(self::ATTRIBUTE);
        } catch(NoSuchEntityException $exception) {
            return null;
        }

        return $attribute;
    }

    public function getAddressType()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->getAttribute() ? (bool)$this->getAttribute()
            ->isRequired() : false;
    }

    public function getFieldId()
    {
        return self::ATTRIBUTE;
    }

    public function getFieldLabel()
    {
        return $this->getAttribute() ? $this->getAttribute()->getFrontendLabel() : __('GstIn Number');
    }

    public function getFieldName()
    {
        return self::ATTRIBUTE;
    }
}
