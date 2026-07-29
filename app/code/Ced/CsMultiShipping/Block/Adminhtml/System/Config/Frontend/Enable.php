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
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultiShipping\Block\Adminhtml\System\Config\Frontend;

class Enable extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * Enable constructor.
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        parent::__construct($context, $data);
    }

    /**
     *  Return element html
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($websitecode = $this->getRequest()->getParam('website')) {
            $website = $this->websiteFactory->create()->load($websitecode);
            if ($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmultishipping/general/activation') ? 0 : 1;
            }
        } else {
            $active = $this->csmarketplaceHelper->getStoreConfig('ced_csmultishipping/general/activation') ? 0 : 1;
        }
        $html = '';
        $html .= $element->getElementHtml();
        $html .= '<script>
				if(' . $active . '){
					document.getElementById("row_' . $element->getHtmlId() . '").style.display="none";
				}
				</script>';
        return $html;
    }
}
