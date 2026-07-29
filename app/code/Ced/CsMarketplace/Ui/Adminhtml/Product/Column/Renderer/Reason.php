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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Ui\Adminhtml\Product\Column\Renderer;

use Magento\Ui\Component\Listing\Columns\Column;


/**
 * Class Reason
 * @package Ced\CsMarketplace\Ui\Adminhtml\Product\Column\Renderer
 */
class Reason extends Column
{

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as $key => $item)
        {
            $html = "N/A";
            if($item['id'] && $item['check_status'] == 0){
                $html = $item['reason'];
            }
            $dataSource['data']['items'][$key]['reason'] = $html;
        }
        return $dataSource;
    }
}
