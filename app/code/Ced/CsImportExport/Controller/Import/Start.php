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
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsImportExport\Controller\Import;

use Ced\CsImportExport\Controller\ImportResult as ImportResultController;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlFactory;

/**
 * Class Start
 * @package Ced\CsImportExport\Controller\Import
 */
class Start extends ImportResultController
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;
    protected $exceptionMessageFactory;

    /**
     * Start constructor.
     * @param \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     * @param \Magento\ImportExport\Model\History $historyModel
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor,
        \Magento\ImportExport\Model\History $historyModel,
        \Magento\ImportExport\Helper\Report $reportHelper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        parent::__construct(
            $reportProcessor,
            $historyModel,
            $reportHelper,
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
        $this->importModel = $importModel;
        $this->exceptionMessageFactory = $exceptionMessageFactory;
    }


    /**
     * Start import process action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /**
             * @var \Magento\Framework\View\Result\Layout $resultLayout
             */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            /**
             * @var $resultBlock \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result
             */
            $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result1');
            $resultBlock
                ->addAction('show', 'import_validation_container')
                ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                ->addAction('hide', ['edit_form', 'upload_button', 'messages']);

            $this->importModel->setData($data);
            $errorAggregator = $this->importModel->getErrorAggregator();
            try {
                $this->importModel->importSource();
            } catch (\Exception $e) {
                $resultMessageBlock = $resultLayout->getLayout()->getBlock('messages');
                $message = $this->exceptionMessageFactory->createMessage($e);
                $html = $resultMessageBlock->addMessage($message)->toHtml();
                //$html = $resultMessageBlock->addMessage($e->getMessage())->toHtml();
                $errorAggregator->addError(
                    \Magento\ImportExport\Model\Import\Entity\AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                    \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                    null,
                    null,
                    null,
                    $html
                );
            }
            if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $resultBlock->addError(__('Maximum error count has been reached or system error is occurred!'));
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                $this->importModel->invalidateIndex();

                $noticeHtml = $this->historyModel->getSummary();

                if ($this->historyModel->getErrorFile()) {
                    $noticeHtml .= '<div class="import-error-wrapper">' . __('Only the first 100 errors are shown. ')
                        . '<a href="'
                        . $this->createDownloadUrlImportHistoryFile($this->historyModel->getErrorFile())
                        . '">' . __('Download full report') . '</a></div>';
                }

                $resultBlock->addNotice(
                    $noticeHtml
                );
                $this->addErrorMessages($resultBlock, $errorAggregator);
                $resultBlock->addSuccess(__('Process successfully done'));
            }
            return $resultLayout;
        }

        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
