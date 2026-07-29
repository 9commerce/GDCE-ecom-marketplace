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


use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Helper\Tool\Image;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Header
 * @package Ced\CsMarketplace\Block\Vendor
 */
class Header extends Template
{

    const XML_PATH_PREFIX_CED = "ced_csmarketplace/";
    const XML_PATH_PREFIX_VSHOP = "ced_vshops/login_page/";

    /**
     * @var Data
     */
    public $_helper;

    /**
     * @var Image
     */
    public $imageHelper;

    /**
     * Header constructor.
     * @param Data $dataHelper
     * @param Image $imageHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $dataHelper,
        Image $imageHelper,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $dataHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get current Store Id.
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Logo
     *
     * @return string
     */
    public function getLogo()
    {
        $logo = $this->imageHelper->getStoreConfig(
            "ced_loginsignup/header/logo",
            $this->getCurrentStoreId()
        );
        return $logo ? "ced/csmarketplace/". $logo : '';
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getCedConfigValue($path) {
        return $this->_helper->getStoreConfig(self::XML_PATH_PREFIX_CED . $path, $this->getCurrentStoreId());
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getVshopConfigValue($path) {
        return $this->_helper->getStoreConfig(self::XML_PATH_PREFIX_VSHOP . $path);
    }

    /**
     * @return mixed
     */
    public function getLoginFormHtml() {
        return $this->getLayout()->createBlock(
            'Ced\CsMarketplace\Block\Vendor\Form\Login'
        )->setTemplate(
            'Ced_CsMarketplace::customer/form/new_login_form.phtml'
        )->toHtml();
    }
}
