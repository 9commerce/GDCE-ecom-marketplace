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

namespace Ced\CsMarketplace\Model;

use Ced\CsMarketplace\Helper\Mail;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\VproductsFactory;
use Magento\Catalog\Model\Product\ActionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Vshop
 * @package Ced\CsMarketplace\Model
 */
class Vshop extends FlatAbstractModel
{

    const ENABLED = 1;
    const DISABLED = 2;

    /**
     * @var Mail
     */
    protected $mail;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vProductsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_customerSession;

    /**
     * Vshop constructor.
     * @param Mail $mail
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param ActionFactory $actionFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vProductsFactory
     * @param Session $marketplaceSession
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mail $mail,
        VendorFactory $vendorFactory,
        ActionFactory $actionFactory,
        VproductsFactory $vProductsFactory,
        Session $marketplaceSession,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mail = $mail;
        $this->vendorFactory = $vendorFactory;
        $this->actionFactory = $actionFactory;
        $this->vProductsFactory = $vProductsFactory;
        $this->_registry = $registry;
        $this->_customerSession = $marketplaceSession->getCustomerSession();

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param array $vendorIds
     * @param $shop_disable
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveShopStatus(array $vendorIds, $shop_disable)
    {
        $vendors = [];
        if (count($vendorIds) > 0) {
            foreach ($vendorIds as $vendorId) {
                $model = $this->loadByField(['vendor_id'], [$vendorId]);
                if ($model && $model->getId()) {
                    if ($model->getShopDisable() != $shop_disable) {
                        $model->setShopDisable($shop_disable)->save();
                        $vendors[] = $vendorId;
                    }
                } else {
                    $this->setVendorId($vendorId)->setShopDisable($shop_disable)->save();
                    $vendors[] = $vendorId;
                }
                $collection = $this->vProductsFactory->create()->getVendorProducts('', $vendorId);
                foreach ($collection as $row) {
                    $productId = $row->getProductId();
                    if ($shop_disable == self::DISABLED) {
                        $this->actionFactory->create()->updateAttributes(
                            [$productId],
                            ['status' => Status::STATUS_DISABLED],
                            0
                        );

                        $this->vProductsFactory->create()->load($productId, 'product_id')
                            ->setCheckStatus(Vproducts::NOT_APPROVED_STATUS)->save();
                    }
                }
                $vendor = $this->vendorFactory->create()->load($vendorId);
                $this->mail->sendShopEmail($shop_disable,$vendor);
            }
        }
        return count($vendors);
    }

    /**
     *Change Products Status (Hide/show products from frontend on vendor approve/disapprove)
     *
     * @params array $vendorIds,int $status
     * @param $vendorIds
     * @param $status
     * @return Vshop
     */
    public function changeProductsStatus($vendorIds, $status)
    {
        if (is_array($vendorIds)) {
            foreach ($vendorIds as $vendorId) {
                $collection = $this->vProductsFactory->create();
                $collection->getVendorProducts('', $vendorId);
                foreach ($collection as $row) {
                    $productId = $row->getProductId();
                    if ($status == self::DISABLED) {
                        $this->actionFactory->create()->updateAttributes([$productId],
                            ['status' => Status::STATUS_DISABLED], 0);
                        $this->vProductsFactory->create()->load($productId, 'product_id')
                            ->setCheckStatus(Vproducts::NOT_APPROVED_STATUS)->save();
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Initialize vproducts model
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMarketplace\Model\ResourceModel\Vshop');
    }
}
