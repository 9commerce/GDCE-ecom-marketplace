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
 * @package     Ced_CsOrder
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Controller\Creditmemo;

class Exportexcel extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Grid action
     * @return void
     */
    public function execute()
    {
        $fileName   = 'creditmemos.xml';
        $grid       = $this->resultPageFactory->create(true)->getLayout()
                      ->createBlock(\Ced\CsOrder\Block\ListCreditmemo\Grid::class);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
