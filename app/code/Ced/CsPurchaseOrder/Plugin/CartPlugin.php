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
 * @package   Ced_CsPurchaseOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Plugin;

use Ced\CsPurchaseOrder\Model\ResourceModel\Purchaseorder;

/**
 * Class CartPlugin
 * @package Ced\CsPurchaseOrder\Plugin
 */
Class CartPlugin
{
    /**
     * @var \Ced\CsPurchaseOrder\Model\PurchaseorderFactory
     */
    protected $purchaseorderFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Purchaseorder
     */
    protected $purchaseorderResource;

    /**
     * CartPlugin constructor.
     * @param \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Purchaseorder $purchaseorderResource
     */
    public function __construct(
        \Ced\CsPurchaseOrder\Model\PurchaseorderFactory $purchaseorderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Purchaseorder $purchaseorderResource
    )
    {
        $this->purchaseorderFactory = $purchaseorderFactory;
        $this->messageManager = $messageManager;
        $this->purchaseorderResource = $purchaseorderResource;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param $data
     * @return array
     */
    public function beforeupdateItems(\Magento\Checkout\Model\Cart $subject, $data)
    {
        $quote = $subject->getQuote();
        if ($quote) {
            foreach ($data as $key => $value) {
                $item = $quote->getItemById($key);
                $purchaseorder = $this->purchaseorderFactory->create();
                $this->purchaseorderResource->load($purchaseorder, $item->getItemId(), 'quote_item_id');
                if ($item->getProductId() == $purchaseorder->getProductId()) {
                    $data[$item->getId()]['qty'] = $item->getQty();
                    $this->messageManager->addNoticeMessage('You can not update quantity of the quote item.');
                }
            }
        }
        return [$data];
    }
}
