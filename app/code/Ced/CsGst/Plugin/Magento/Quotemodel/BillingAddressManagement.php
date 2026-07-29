<?php

namespace Ced\CsGst\Plugin\Magento\Quotemodel;

class BillingAddressManagement
{
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