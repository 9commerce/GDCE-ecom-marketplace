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

namespace Ced\CsMarketplace\Controller\Vpayments;

use Ced\CsMarketplace\Helper\Payment;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class ExportVpaymentCsv
 * @package Ced\CsMarketplace\Controller\Vpayments
 */
class ExportCsv extends \Magento\Framework\App\Action\Action
{
     /**
     * @var Payment
     */
    public $_payment;
    /**
     * @var FileFactory
     */
    protected $_fileFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * ExportVpaymentCsv constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FileFactory $fileFactory
     * @param Payment $payment
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        Payment $payment
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_fileFactory = $fileFactory;
        $this->_payment = $payment;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $filename = 'vendor_transactions.csv';
        $content = $this->_payment->getVendorCommision();
        $this->_prepareDownloadResponse($filename, $content);

    }


}
