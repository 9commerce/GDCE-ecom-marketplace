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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Exportcsv extends \Ced\CsMarketplace\Controller\Vendor
{
    const CSV_FILE_NAME   = 'creditmemos.csv';

    /**
     * Grid action
     * @return void
     */
    public function execute()
    {
        $grid       = $this->resultPageFactory->create(true)->getLayout()
                      ->createBlock(\Ced\CsOrder\Block\ListCreditmemo\Grid::class);
        $this->_prepareDownloadResponse(self::CSV_FILE_NAME, $grid->getCsvFile());
    }
}
