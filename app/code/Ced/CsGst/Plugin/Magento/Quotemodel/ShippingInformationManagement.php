<?php

namespace Ced\CsGst\Plugin\Magento\Quotemodel;
/*
 * used to save data in quote table
 * */
class ShippingInformationManagement
{

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $address
     */
    public function beforeAssign(
        $subject,
        $cartId,
        $address
    ) {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            try {
                $GstinNumber = $extAttributes->getGstinNumber();
                $address->setGstinNumber($GstinNumber);
            } catch (\Exception $e) { 

            }
        }
    }
}
