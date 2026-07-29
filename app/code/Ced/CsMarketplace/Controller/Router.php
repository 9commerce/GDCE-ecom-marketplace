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

namespace Ced\CsMarketplace\Controller;

/**
 * Class Router
 * @package Ced\CsMarketplace\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $manager;

    /**
     * Router constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Module\Manager $manager
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Module\Manager $manager
    ) {
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->manager = $manager;
    }

    /**
     * Validate and Match
     *
     * @param  \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $custom_suffix = $this->csmarketplaceHelper
            ->getStoreConfig('ced_vseo/general/marketplace_url_suffix');
        $suffix = $custom_suffix ? $custom_suffix : \Ced\CsMarketplace\Model\Vendor::VENDOR_SHOP_URL_SUFFIX;
        $url_path = 'vendor_shop/';
        if ($this->manager->isEnabled('Ced_CsSeoSuite')) {
            $csseosuiteHelper = \Magento\Framework\App\ObjectManager::getInstance()->get('Ced\CsSeoSuite\Helper\Data');
            if ($csseosuiteHelper->isEnabled())
                $url_path = $this->csmarketplaceHelper->getStoreConfig('ced_vseo/general/marketplace_url_key') . '/';
        }

        if (strpos($identifier, $url_path) !== false && strpos($identifier, $suffix) !== false) {
            $urls = explode('/', $identifier);
            $url = explode($suffix, end($urls));
            $request->setModuleName('csmarketplace')->setControllerName('vshops')->setActionName('view')
                ->setParam('shop_url', $url[0]);
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        }
        return false;
    }
}
