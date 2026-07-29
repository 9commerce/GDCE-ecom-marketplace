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

use Ced\CsEnhancement\Helper\Attribute;
use Ced\CsEnhancement\Helper\Csv;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ExportCsvFormat
 * @package Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class ExportCsvFormat extends Action
{
    /**
     * @var Attribute
     */
    protected $attributeHelper;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * ExportCsvFormat constructor.
     * @param Attribute $attributeHelper
     * @param Csv $csv
     * @param FileFactory $fileFactory
     * @param Action\Context $context
     */
    public function __construct(
        Attribute $attributeHelper,
        Csv $csv,
        FileFactory $fileFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->attributeHelper = $attributeHelper;
        $this->csv = $csv;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'VendorImport';

        $dataRows = $this->getDataRows();
        $createFile = $this->csv->createCsv($fileName, $dataRows);
        $content = [];

        if (!empty($createFile['success'])) {
            $content['type'] = 'filename'; // must keep filename
            $content['value'] = $createFile['path'];
            $content['rm'] = '1'; //remove csv from var folder
        }
        $csv_file_name = ucfirst($fileName) . 'Format.csv';
        return $this->_fileFactory->create($csv_file_name, $content, DirectoryList::VAR_DIR);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getDataRows()
    {
        $registrationAttributes = $result = [];
        try {
            $registrationAttributes = $this->attributeHelper->getRegistrationAttributes();
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }

        $i = 0;
        $customerAttributes = $this->attributeHelper->getCustomerFormAttributes();

        foreach ([$customerAttributes, $registrationAttributes] as $attributes) {
            foreach ($attributes as $attribute) {
                /** @var \Ced\CsMarketplace\Model\Vendor\Attribute $attribute */
                $result[0][$i++] = $attribute->getAttributeCode();
                $result[1][$i++] = $this->getRegistrationAttributesDummyValue($attribute);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return string
     */
    public function getRegistrationAttributesDummyValue($attribute)
    {
        $result = '';
        if (is_object($attribute) && !empty($attribute->getAttributeCode())) {
            $code = $attribute->getAttributeCode();
            switch ($code) {
                case 'email':
                    $result = 'abc@domain.com';
                    break;

                case 'country_id':
                    $result = 'IN';
                    break;

                case 'region_id':
                    $result = 'MH';
                    break;

                case 'region':
                    $result = 'Maharashtra';
                    break;

                case 'zip_code':
                    $result = '110011';
                    break;
            }

            if (empty($result) && !empty($attribute->getFrontend()->getClass())) {
                $frontendClass = $attribute->getFrontend()->getClass();
                switch ($frontendClass) {
                    case 'validate-email':
                        $result = 'abc@domain.com';
                        break;

                    case 'validate-digits':
                    case 'validate-number':
                        $result = '123';
                        break;

                    case 'validate-alpha':
                        $result = 'abc';
                        break;

                    case 'validate-alphanum':
                        $result = 'abc123';
                        break;
                }
            }
        }

        return $result;
    }
}
