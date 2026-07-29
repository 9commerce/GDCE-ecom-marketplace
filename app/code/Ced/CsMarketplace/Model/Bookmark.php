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

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Ced\CsMarketplace\Api\Data\BookmarkInterface;
use Ced\CsMarketplace\Model\ResourceModel\Bookmark\Collection;
use Ced\CsMarketplace\Model\ResourceModel\Bookmark as ResourceBookmark;

/**
 * Domain class Bookmark
 * @codeCoverageIgnore
 */
class Bookmark extends AbstractExtensibleModel implements BookmarkInterface
{
    /**
     * @var DecoderInterface
     * @deprecated 101.1.0
     */
    protected $cedjsonDecoder;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ResourceBookmark $resource
     * @param Collection $resourceCollection
     * @param DecoderInterface $cedjsonDecoder
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ResourceBookmark $resource,
        Collection $resourceCollection,
        DecoderInterface $cedjsonDecoder,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->jsonDecoder = $cedjsonDecoder;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
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
     * Get Id of Marketplace Bookmark
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::BOOKMARK_ID);
    }

    /**
     * Get user Id of Marketplace Bookmark
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Get namespace of Marketplace Bookmark
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->getData(self::BOOKMARKSPACE);
    }

    /**
     * Get identifier of Marketplace Bookmark
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Is current of Marketplace Bookmark
     *
     * @return bool
     */
    public function isCurrent()
    {
        return (bool)$this->getData(self::CURRENT);
    }

    /**
     * Get title of Marketplace Bookmark
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get config of Marketplace Bookmark
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->getData(self::CONFIG);
        if ($config) {
            return $this->serializer->unserialize($config);
        }
        return [];
    }

    /**
     * Get created at of Marketplace Bookmark
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at of Marketplace Bookmark
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Id of Marketplace Bookmark
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::BOOKMARK_ID, $id);
    }

    /**
     * Set user Id of Marketplace Bookmark
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Set namespace of Marketplace Bookmark
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        return $this->setData(self::BOOKMARKSPACE, $namespace);
    }

    /**
     * Set identifier of Marketplace Bookmark
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set current of Marketplace Bookmark
     *
     * @param bool $isCurrent
     * @return $this
     */
    public function setCurrent($isCurrent)
    {
        return $this->setData(self::CURRENT, $isCurrent);
    }

    /**
     * Set title of Marketplace Bookmark
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /** 
     * Set config of Marketplace Bookmark
     *
     * @param string $config
     * @return $this
     */
    public function setConfig($config)
    {
        return $this->setData(self::CONFIG, $config);
    }

    /**
     * Set created at of Marketplace Bookmark
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at of Marketplace Bookmark
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Ui\Api\Data\BookmarkExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
