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
 * @package   Ced_CsCommission
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCommission\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Ced\CsCommission\Helper\Category as categoryHelper;

class VendorSave implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var ManagerInterface
     */
    protected $_massageManager;

    /** @var categoryHelper  */
    protected $_categoryHelper;

    /**
     * VendorSave constructor.
     * @param RequestInterface $request
     * @param Session $authSession
     * @param ConfigFactory $configFactory
     * @param ManagerInterface $MassageManagerInterface
     */
    public function __construct(
        RequestInterface $request,
        Session $authSession,
        ConfigFactory $configFactory,
        ManagerInterface $MassageManagerInterface,
        categoryHelper $categoryHelper
    ) {
        $this->request = $request;
        $this->_authSession = $authSession;
        $this->_configFactory = $configFactory;
        $this->_massageManager = $MassageManagerInterface;
        $this->_categoryHelper = $categoryHelper;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($this->_authSession->isLoggedIn()) {
            $params = $this->request->getPostValue();
            $vendorId = $this->request->getParam('vendor_id');
            $params = $this->_categoryHelper->setCategoryWiseCommissionConfig(
                $params,
                categoryHelper::TYPE_DEFAULT,
                categoryHelper::DEFAULT_TYPE_ID,
                $vendorId
            );
            $params['section'] = 'ced_csmarketplace';
            $params['is_csgroup'] = 2;
            $website = $this->request->getParam('website');
            $store = $this->request->getParam('store');

            try {
                $configData = [
                    'section' => $params['section'],
                    'website' => $website,
                    'store' => $store,
                    'groups' => $params['groups'] ?? [],
                ];
                /** @var  $configModel */
                $configModel = $this->_configFactory->create(['data' => $configData]);
                $configModel->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages = explode("\n", $e->getMessage());
                foreach ($messages as $message) {
                    $this->_massageManager->addErrorMessage($message);
                }
            } catch (\Exception $e) {
                $this->_massageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving this configuration:') . ' ' . $e->getMessage()
                );
            }
        }
        return $this;
    }
}
