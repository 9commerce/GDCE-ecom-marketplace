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

/**
 * Class Form
 * @package Ced\CsMarketplace\Model
 */
class Form extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var array
     */
    public static $VENDOR_FORM_READONLY_ATTRIBUTES = array(
        'shop_url',
        'created_at',
        'status',
        'group',
        'email'
    );

    /**
     * @var array
     */
    public static $VENDOR_FORM_EDITABLE_ATTRIBUTES = array(
        'shop_url',
        'name',
        'gender',
        'profile_picture',
        'contact_number',
        'company_name',
        'about',
        'company_logo',
        'company_banner',
        'company_address',
        'support_number',
        'support_email'
    );

    /**
     * @var array
     */
    public static $VENDOR_PROFILE_RESTRICTED_ATTRIBUTES = array(
        'customer_id',
    );

    /**
     * @var array
     */
    public static $VENDOR_REGISTRATION_RESTRICTED_ATTRIBUTES = array(
        'customer_id',
        'name',
        'gender',
        'created_at',
        'website_id',
        'email'
    );
}
