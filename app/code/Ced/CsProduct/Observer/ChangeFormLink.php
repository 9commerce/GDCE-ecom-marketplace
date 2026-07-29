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
 * @category  Ced
 * @package   Ced_CsProduct
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class ChangeFormLink implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var \Ced\CsProduct\Helper\Data
     */
    protected $csproductHelper;

    /**
     * ChangeFormLink constructor.
     * @param RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Ced\CsProduct\Helper\Data $csproductHelper
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\UrlInterface $urlManager,
        \Ced\CsProduct\Helper\Data $csproductHelper
    ) {
        $this->request = $request;
        $this->_urlManager = $urlManager;
        $this->csproductHelper = $csproductHelper;
    }

    /**
     * redirect on advance product link
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->csproductHelper->isActive()) {
            $controller = $observer->getControllerAction();
            $url = $this->_urlManager->getUrl('csproduct/vproducts/new', ['set' => 4, 'type' => 'simple']);
            $this->request->setModuleName('csproduct');
            $this->request->setPostValue('set', 4);
            $this->request->setPostValue('type', 'simple');
            $controller->getResponse()->setRedirect($url);
        }
    }
}
