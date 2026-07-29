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

namespace Ced\CsMarketplace\Block\Vendor;


/**
 * Class Navigation
 * @package Ced\CsMarketplace\Block\Vendor
 */
class Navigation extends AbstractBlock
{

    /**
     * @var bool
     */
    protected $_activeLink = false;

    /**
     * @param $path
     * @return $this
     */
    public function setActive($path)
    {
        $this->_activeLink = $this->_completePath($path);
        return $this;
    }

    /**
     * @param $path
     * @return string
     */
    protected function _completePath($path)
    {
        $path = rtrim($path, '/');
        switch (count(explode('/', $path))) {
            case 1:
            case 2:
                $path .= '/index';
                break;
        }
        return $path;
    }

    /**
     * @param $link
     * @return bool
     */
    public function isActive($link)
    {
        if (empty($this->_activeLink))
            $this->_activeLink = $this->getAction()->getFullActionName('/');

        if ($this->_completePath($link->getPath()) == $this->_activeLink)
            return true;

        if (count($link->getChildren()) > 0) {
            $isParentActive = false;
            foreach ($link->getChildren() as $ch1_link) {
                if ($this->isActive($ch1_link)) {
                    $isParentActive = true;
                    break;
                }
            }
            return $isParentActive;
        }

        return false;
    }

    /**
     * @return int
     */
    public function isPaymentDetailAvailable()
    {
        return count($this->getVendor()->getPaymentMethodsArray($this->getVendorId(), false));
    }
}
