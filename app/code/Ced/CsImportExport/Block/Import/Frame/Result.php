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
 * @category  Ced
 * @package   Ced_CsImportExport
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsImportExport\Block\Import\Frame;

class Result extends \Magento\Backend\Block\Template
{

    protected $_actions = [
        'clear' => [],
        'innerHTML' => [],
        'value' => [],
        'show' => [],
        'hide' => [],
        'removeClassName' => [],
        'addClassName' => [],
    ];

    protected $_messages = ['error' => [], 'success' => [], 'notice' => []];

    protected $_jsonEncoder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    
    public function _construct()
    {
        $this->setTemplate('Ced_CsImportExport::import/frame/result.phtml');
    }
    
    
    public function addAction($actionName, $elementId, $value = null)
    {
        if (isset($this->_actions[$actionName])) {
            if (null === $value) {
                if (is_array($elementId)) {
                    foreach ($elementId as $oneId) {
                        $this->_actions[$actionName][] = $oneId;
                    }
                } else {
                    $this->_actions[$actionName][] = $elementId;
                }
            } else {
                $this->_actions[$actionName][$elementId] = $value;
            }
        }
        return $this;
    }


    public function addError($message)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addError($row);
            }
        } else {
            $this->_messages['error'][] = $message;
        }
        return $this;
    }

    
    public function addNotice($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addNotice($row);
            }
        } else {
            $this->_messages['notice'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }


    public function addSuccess($message, $appendImportButton = false)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addSuccess($row);
            }
        } else {
            $this->_messages['success'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }


    public function getImportButtonHtml()
    {
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' .
            $this->getImportStartUrl() .
            '\', \'' .
            \Magento\ImportExport\Model\Import::FIELD_NAME_SOURCE_FILE .
            '\');" class="scalable save"' .
            ' type="button"><span><span><span>' .
            __(
                'Import'
            ) . '</span></span></span></button>';
    }


    public function getImportStartUrl()
    {
        return $this->getUrl('*/*/start');
    }


    public function getMessages()
    {
        return $this->_messages;
    }


    public function getMessagesHtml()
    {
        $messagesBlock = $this->_layout->createBlock('Magento\Framework\View\Element\Messages');

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->{$method}($message);
            }
        }
        return $messagesBlock->toHtml();
    }


    public function getResponseJson()
    {
        if (!isset($this->_actions['import_validation_messages'])) {
            $this->addAction('innerHTML', 'import_validation_messages', $this->getMessagesHtml());
        }
        return $this->_jsonEncoder->encode($this->_actions);
    }
}
