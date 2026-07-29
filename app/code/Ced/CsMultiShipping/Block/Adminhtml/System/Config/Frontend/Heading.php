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

class Heading extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMultiShipping\Model\Source\Shipping\Methods
     */
    protected $methods;

    /**
     * Heading constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMultiShipping\Model\Source\Shipping\Methods $methods
    ) {
        $this->_request = $request;
        $this->websiteFactory = $websiteFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->methods = $methods;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $active = 1;
        if ($websitecode = $this->_request->getParam('website')) {
            $website = $this->websiteFactory->create()->load($websitecode);
            if ($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmultishipping/general/activation') ? 1 : 0;
            }
        } else {
            $active = $this->csmarketplaceHelper->getStoreConfig('ced_csmultishipping/general/activation') ? 1 : 0;
        }

        $methods = $this->methods->getMethods();
        $count = 0;
        if (count($methods) > 0) {
            $count = 1;
        }
        $validation = $active && $count ? 0 : 1;

        $html = '';
        $html .= sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $element->getLabel()
        );
        $html .= '<script>
				if(' . $validation . '){
					document.getElementById("row_' . $element->getHtmlId() . '").style.display="none";
				}
				</script>';
        return $html;
    }
}
