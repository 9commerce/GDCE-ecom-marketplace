<?php 

 /**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Gst
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsGst\Model\Config\Source;
 
class Rate extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {

	public function getAllOptions()
    {
        return [
        	[ 'label' => __('Please select'), 'value' =>  0],
			['label' => __('0.25%'), 'value' =>  0.25],
			['label' => __('3%'), 'value' =>  3],
			['label' => __('5%'), 'value' =>  5],
			['label' => __('12%'), 'value' =>  12],
			['label' => __('18%'),'value' =>  18],
			['label' => __('28%'),'value' =>  28]
        ];
    }

	public function toOption()
	{
		return $this->getAllOptions();
	}
}