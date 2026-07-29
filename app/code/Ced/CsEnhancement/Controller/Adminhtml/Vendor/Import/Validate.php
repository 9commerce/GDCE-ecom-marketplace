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

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;

use Magento\Backend\App\Action;
use Magento\Framework\File\Csv;

/**
 * Class Validate
 * @package Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class Validate extends Action
{

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonEncoder;

    /**
     * Validate constructor.
     * @param Csv $csv
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonEncoder
     * @param Action\Context $context
     */
    public function __construct(
        Csv $csv,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->csv = $csv;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     */
    public function execute()
    {
        $csvData = [];
        try {
            $filePath = $this->getRequest()->getParam('path');
            $csvData = $this->csv->getData($filePath);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->getResponse()->setBody($this->jsonEncoder->serialize($csvData));
    }
}
