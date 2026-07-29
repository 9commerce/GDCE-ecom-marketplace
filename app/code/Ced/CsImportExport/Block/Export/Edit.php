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
namespace Ced\CsImportExport\Block\Export;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'export_id';
        $this->_blockGroup = 'Ced_CsImportExport';
        $this->_controller = 'export';
        $this->setData('area', 'adminhtml');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Export');
    }
    
    protected function _prepareLayout()
    {
        $this->setTemplate('Ced_CsImportExport::form/container.phtml');
        return parent::_prepareLayout();
    }
}
