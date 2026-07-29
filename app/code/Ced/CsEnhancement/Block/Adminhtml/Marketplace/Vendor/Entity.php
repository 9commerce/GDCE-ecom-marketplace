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
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Block\Adminhtml\Marketplace\Vendor;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Entity
 * @package Ced\CsEnhancement\Block\Adminhtml\Marketplace\Vendor
 */
class Entity extends \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function getAddButtonOptions()
    {
        parent::getAddButtonOptions();
        $importButtonOptions = [];
        if ($this->isVendorEnhancementEnabled()) {
            $importButtonOptions = [
                'label' => __('Import Vendors'),
                'class' => 'default',
                'onclick' => "setLocation('" . $this->getImportUrl() . "')"
            ];
        }
        $this->buttonList->add('import', $importButtonOptions);
    }

    /**
     * @return string
     */
    protected function getImportUrl()
    {
        return $this->getUrl('csenhancement/vendor/import');
    }

    /**
     * @return mixed
     */
    public function isVendorEnhancementEnabled()
    {
        return $this->scopeConfig->getValue(
            'ced_csenhancement/general/csenhancements_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
