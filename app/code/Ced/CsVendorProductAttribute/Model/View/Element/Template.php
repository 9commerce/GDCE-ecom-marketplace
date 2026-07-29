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
  * @package   Ced_CsVendorProductAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsVendorProductAttribute\Model\View\Element;

use Magento\Framework\View\Element\Template as FrameworkTemplate;
/**
 * Class Template
 * @package Ced\CsVendorProductAttribute\Model\View\Element
 */
class Template extends FrameworkTemplate
{
    /**
     * @param null $template
     * @return mixed
     */
    public function getTemplateFile($template = null)
    {
        $params = ['module' => $this->getModuleName()];
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }

        if($this->getTemplate()=="Magento_ProductVideo::product/edit/base_image.phtml" ||
            $this->getTemplate()=="Magento_Catalog::catalog/wysiwyg/js.phtml" ||
        	$this->getTemplate()=="Magento_Backend::widget/button.phtml" ||
			$this->getTemplate()=="Magento_Backend::widget/grid/extended.phtml" ||
            $this->getTemplate()=="Magento_Backend::pageactions.phtml" ||
			$this->getTemplate()=="Magento_ConfigurableProduct::product/configurable/stock/disabler.phtml" ||
			$this->getTemplate()=="Magento_ConfigurableProduct::product/configurable/affected-attribute-set-selector/form.phtml")
        {
        	$params['area'] = 'adminhtml';
        }
        return $this->resolver->getTemplateFileName($template ?: $this->getTemplate(), $params);
    }
}
