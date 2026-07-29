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
namespace Ced\CsMarketplace\Api;

/**
 * Interface for managing bookmarks
 *
 * @api
 * @since 100.0.2
 */
interface BookmarkManagementInterface
{
    /**
     * Retrieve list of bookmarks by namespace
     *
     * @param string $namespace
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface[]
     */
    public function loadByNamespace($namespace);

    /**
     * Retrieve bookmark by identifier and namespace
     *
     * @param string $identifier
     * @param string $namespace
     * @return \Ced\CsMarketplace\Api\Data\BookmarkInterface
     */
    public function getByIdentifierNamespace($identifier, $namespace);
}
