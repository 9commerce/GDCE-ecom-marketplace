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
namespace Ced\CsCommission\Plugin\System\Config;

use Ced\CsCommission\Helper\Category as categoryHelper;

class Save
{
    const TYPE_WEBSITE = 'website';
    const TYPE_STORE = 'store';

    /** @var categoryHelper  */
    protected $_categoryHelper;

    /**
     * Save constructor.
     * @param categoryHelper $categoryHelper
     */
    public function __construct(
        categoryHelper $categoryHelper
    ) {
        $this->_categoryHelper = $categoryHelper;
    }

    /**
     * @param $subject
     */
    public function beforeExecute($subject)
    {
        $postData = $subject->getRequest()->getPostValue();
        if ($websiteId = $subject->getRequest()->getParam('website')) {
            if (isset($postData['groups']['vpayments']['fields']) &&
                !isset(['groups']['vpayments']['fields']['commission_cw']['inherit'])) {
                $data = $this->_categoryHelper->setCategoryWiseCommissionConfig(
                    $postData,
                    self::TYPE_WEBSITE,
                    $websiteId,
                    categoryHelper::DEFAULT_VENDOR_ID
                );
                $subject->getRequest()->setPostValue($data);
            }
        } elseif ($storeId = $subject->getRequest()->getParam('store')) {
            if (isset($postData['groups']['vpayments']['fields']) &&
                !isset($postData['groups']['vpayments']['fields']['commission_cw']['inherit'])) {
                $data = $this->_categoryHelper->setCategoryWiseCommissionConfig(
                    $postData,
                    self::TYPE_STORE,
                    $storeId,
                    categoryHelper::DEFAULT_VENDOR_ID
                );
                $subject->getRequest()->setPostValue($data);
            }
        } else {
            $data = $this->_categoryHelper->setCategoryWiseCommissionConfig(
                $postData,
                categoryHelper::TYPE_DEFAULT,
                categoryHelper::DEFAULT_TYPE_ID,
                categoryHelper::DEFAULT_VENDOR_ID
            );
            $subject->getRequest()->setPostValue($data);
        }
    }
}
