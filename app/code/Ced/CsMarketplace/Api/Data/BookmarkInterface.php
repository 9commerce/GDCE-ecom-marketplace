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
namespace Ced\CsMarketplace\Api\Data;

/**
 * Bookmark interface
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkInterface extends \Magento\Ui\Api\Data\BookmarkExtensionInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case of CsMarkatplace
     */
    const BOOKMARK_ID      = 'bookmark_id';
    const USER_ID          = 'user_id';
    const BOOKMARKSPACE    = 'namespace';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const CONFIG           = 'config';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';
    const CURRENT          = 'current';
    /**#@-*/

    /**
     * Get ID of Markatplace Bookmark
     *
     * @return int
     */
    public function getId();

    /**
     * Get user id of Markatplace Bookmark
     *
     * @return int
     */
    public function getUserId();

    /**
     * Get identifier of Markatplace Bookmark
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Get identifier of Markatplace Bookmark
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get title of Markatplace Bookmark
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get config content of Markatplace Bookmark
     *
     * @return array
     */
    public function getConfig();

    /**
     * Get creation time of Markatplace Bookmark
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get update time of Markatplace Bookmark
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get user bookmark is current of Markatplace Bookmark
     *
     * @return bool
     */
    public function isCurrent();

    /**
     * Set ID of Markatplace Bookmark
     *
     * @param int $id
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setId($id);

    /**
     * Set user id of Markatplace Bookmark
     *
     * @param int $userId
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setUserId($userId);

    /**
     * Set namespace of Markatplace Bookmark
     *
     * @param string $namespace
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setNamespace($namespace);

    /**
     * Set identifier of Markatplace Bookmark
     *
     * @param string $identifier
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set title of Markatplace Bookmark
     *
     * @param string $title
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setTitle($title);

    /**
     * Set config content of Markatplace Bookmark
     *
     * @param string $config
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setConfig($config);

    /**
     * Set creation time of Markatplace Bookmark
     *
     * @param string $createdAt
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set update time of Markatplace Bookmark
     *
     * @param string $updatedAt
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set bookmark to current of Markatplace Bookmark
     *
     * @param bool $isCurrent
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function setCurrent($isCurrent);

    /**
     * Retrieve existing extension attributes object or create a new one of Markatplace
     *
     * @return \Magento\Ui\Api\Data\BookmarkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object of Markatplace Bookmark
     *
     * @param \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Ui\Api\Data\BookmarkExtensionInterface $extensionAttributes
    );
}
